<?php

declare(strict_types=1);

namespace Components\Hotfire;

use Components\Hotfire\Exception\InvalidComponentPropertyException;
use Components\Hotfire\Exception\InvalidNamespaceException;
use Components\Hotfire\Exception\StubTemplateNotFoundException;

/**
 * Scaffolds Hotfire reactive components for CodeIgniter 4 apps.
 *
 * Turns a dotted/slashed component name into the Livewire 4 "voltage" layout,
 * adapted for Hotfire — one self-contained folder per component, marked with
 * the 🔥 indicator (default; change via --emoji / the constructor):
 *
 *   php spark make:hotfire post.create --mfc --js --css --test
 *
 *   app/Views/components/hotfire/post/🔥create/
 *   ├── create.php            # class extends Components\Hotfire\Component
 *   ├── create.view.php       # template ($component + hot:* directives)
 *   ├── create.js             # optional (--js)
 *   ├── create.css            # optional scoped (--css)
 *   ├── create.global.css     # optional (--global-css)
 *   └── create.test.php       # optional PHPUnit (--test)
 *
 * Every artifact of a component cohabits its folder; the class declares an
 * explicit $view so the engine renders exactly the generated template.
 * Components named "BottomLogout" become 🔥bottom-logout/ (camelCase → kebab).
 *
 * This class is framework-free: the command wrappers (Components\Commands)
 * only handle CLI plumbing and delegate all content/path logic here.
 */
final class ComponentGenerator
{
    private readonly ComponentPaths $paths;

    /**
     * @param string      $viewsRoot    Root of the app views folder (Hotfire prefix lives under it).
     * @param string      $namespace    Root namespace for generated classes (default "App\Components").
     * @param string      $templatesDir Folder with the *.stub scaffolds consumed by this generator.
     * @param string      $emoji        Visual indicator prefix on each component folder (default "🔥").
     */
    public function __construct(
        private readonly string $viewsRoot,
        private readonly string $namespace = 'App\\Components',
        private readonly string $templatesDir = __DIR__.'/templates',
        string $emoji = '🔥',
    ) {
        if (! self::isValidNamespace($namespace)) {
            throw new InvalidNamespaceException($namespace);
        }

        // ComponentPaths validates the emoji, so both share one rule.
        $this->paths = new ComponentPaths(null, $emoji);
    }

    /**
     * True when $namespace is a backslash-joined list of PHP identifiers
     * (App\Components, Domain\Posts): what a generated class can declare.
     */
    private static function isValidNamespace(string $namespace): bool
    {
        $namespace = trim($namespace, '\\');
        if ($namespace === '') {
            return false;
        }

        foreach (explode('\\', $namespace) as $part) {
            if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $part) !== 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * Splits a component name into path segments.
     *
     * @return list<string>
     *
     * @throws \InvalidArgumentException On malformed or unsafe names.
     */
    public function segments(string $name): array
    {
        return $this->paths->segments($name);
    }

    /** Engine view name of the template ("...create.view", ".php" appended at render time). */
    public function viewRelative(string $name): string
    {
        return $this->paths->viewRelative($name);
    }

    /** Absolute path of the component template (".view.php" appended). */
    public function viewPath(string $name): string
    {
        return rtrim($this->viewsRoot, '/\\').'/'.$this->viewRelative($name).'.php';
    }

    /** Class name for the last segment (kebab → PascalCase). */
    public function className(string $name): string
    {
        $segments = $this->paths->segments($name);

        return ComponentPaths::pascal(array_pop($segments));
    }

    /**
     * Full class namespace for a name below the configured root namespace.
     * "post.create" with root "App\Components" → "App\Components\Post".
     */
    public function classNamespace(string $name): string
    {
        $segments = $this->paths->segments($name);
        array_pop($segments);

        $sub = array_map(static fn (string $segment): string => ComponentPaths::pascal($segment), $segments);

        $ns = trim($this->namespace, '\\');
        if ($sub !== []) {
            $ns .= '\\'.implode('\\', $sub);
        }

        return $ns;
    }

    /** Absolute path of the component class, collocated in its own folder. */
    public function classPath(string $name): string
    {
        return rtrim($this->viewsRoot, '/\\').'/'.$this->paths->classRelative($name);
    }

    /**
     * Absolute path of an optional sidecar (js, css, global.css) sitting next
     * to the template in the component folder.
     */
    public function sidecarPath(string $name, string $suffix): string
    {
        $paths = $this->paths;

        return rtrim($this->viewsRoot, '/\\').'/'.$paths->folder($name).'/'.$paths->leafKebab($name).'.'.ltrim($suffix, '.');
    }

    /** Absolute path of the (optional) PHPUnit test, collocated with the component. */
    public function testPath(string $name): string
    {
        $paths = $this->paths;

        return rtrim($this->viewsRoot, '/\\').'/'.$paths->folder($name).'/'.$paths->leafKebab($name).'.test.php';
    }

    /**
     * Parses --props into a list of valid public property identifiers.
     *
     * @return list<string>
     *
     * @throws \InvalidArgumentException On invalid identifiers.
     */
    public function props(string $raw): array
    {
        $out = [];
        foreach (preg_split('/\s*,\s*/', trim($raw)) ?: [] as $prop) {
            if ($prop === '') {
                continue;
            }
            if (! preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $prop)) {
                throw new InvalidComponentPropertyException($prop);
            }
            $out[] = $prop;
        }

        return array_values($out);
    }

    /**
     * Component class source.
     *
     * @param list<string> $props
     */
    public function classContent(string $name, array $props): string
    {
        return $this->stub('component_class.stub', [
            'namespace'    => $this->classNamespace($name),
            'class'        => $this->className($name),
            'view'         => $this->viewRelative($name),
            'propsAndSave' => $this->propsAndSaveBlock($props),
        ]);
    }

    /**
     * Component template source. With declared props a two-field form is
     * scaffolded (title + content inputs via hot:model and a hot:click save
     * button); without props a plain placeholder root is generated.
     *
     * @param list<string> $props
     */
    public function viewContent(string $name, array $props): string
    {
        if ($props === []) {
            return $this->stub('component_view_plain.stub', [
                'label' => $this->className($name),
            ]);
        }

        $fields = '';
        foreach ($props as $prop) {
            $fields .= $this->stub('component_view_field.stub', [
                'title' => ucfirst(str_replace(['_', '-'], ' ', $prop)),
                'prop'  => $prop,
            ]);
        }

        return $this->stub('component_view.stub', ['fields' => $fields]);
    }

    /** Optional scoped JavaScript next to the component template. */
    public function jsContent(string $name): string
    {
        return $this->stub('component_js.stub', [
            'view'  => $this->viewRelative($name),
            'class' => $this->className($name),
        ]);
    }

    /** Optional scoped stylesheet next to the component template. */
    public function cssContent(string $name): string
    {
        return $this->stub('component_css.stub', ['class' => $this->className($name)]);
    }

    /** Optional global stylesheet (unscoped) next to the component template. */
    public function globalCssContent(string $name): string
    {
        return $this->stub('component_global_css.stub', ['class' => $this->className($name)]);
    }

    /** Optional PHPUnit test source for the component. */
    public function testContent(string $name): string
    {
        $class = $this->className($name);

        // Test namespace mirrors the class namespace under "Tests":
        // post.create → App\Components\Post\Create → Tests\Components\Post.
        $parts = explode('\\', $this->classNamespace($name));
        $parts[0] = 'Tests';
        $testNs = implode('\\', $parts);

        return $this->stub('component_test.stub', [
            'testNamespace' => $testNs,
            'classFqcn'     => $this->classNamespace($name).'\\'.$class,
            'class'         => $class,
        ]);
    }

    /**
     * Block inserted between the $view property and the class close brace:
     * one blank line, the public props (a blank line between each) and the
     * save() action when props were requested. Empty for class-less scaffolds.
     *
     * @param list<string> $props
     */
    private function propsAndSaveBlock(array $props): string
    {
        if ($props === []) {
            return '';
        }

        $properties = implode("\n\n", array_map(
            static fn (string $prop): string => "    public string \${$prop} = '';",
            $props,
        ));
        $save = rtrim($this->stub('component_action_save.stub', []))."\n";

        return $properties."\n\n".$save;
    }

    /**
     * Loads a *.stub scaffold and fills every token placeholder. Callers pass
     * plain keys ("namespace"); the {{ }} braces are added here, so a
     * placeholder always looks like {{namespace}} in the stub files.
     *
     * Unknown placeholders are left untouched so partial stubs stay valid;
     * overwriting one of the shipped stubs is how you customize the scaffold.
     */
    private function stub(string $file, array $tokens): string
    {
        $path = rtrim($this->templatesDir, '/\\').'/'.$file;
        if (! is_readable($path)) {
            throw new StubTemplateNotFoundException($file);
        }

        $content = file_get_contents($path);
        if ($content === false) {
            throw new StubTemplateNotFoundException($file);
        }

        $map = [];
        foreach ($tokens as $key => $value) {
            $map['{{'.$key.'}}'] = $value;
        }

        $out = strtr($content, $map);

        return $out === '' ? '' : rtrim($out, "\n")."\n";
    }
}