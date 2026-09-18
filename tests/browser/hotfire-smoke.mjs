import { mkdtempSync, writeFileSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join, resolve } from 'node:path';
import { spawnSync } from 'node:child_process';

const candidates = ['chromium', 'chromium-browser', 'google-chrome'];
const browser = candidates.find((name) => spawnSync(name, ['--version'], { stdio: 'ignore' }).status === 0);

if (!browser) {
    console.log('Hotfire browser smoke skipped: no Chromium-compatible browser found.');
    process.exit(0);
}

const dir = mkdtempSync(join(tmpdir(), 'hotfire-browser-'));
const app = resolve('js/app.js');
const html = join(dir, 'hotfire.html');

writeFileSync(html, `<!doctype html>
<html>
<body>
    <div id="root" data-hot-component data-hot-snapshot="seed" data-hot-checksum="ok" data-hot-action="/hot">
        <button id="run" data-hot-click="increment">Run</button>
    </div>
    <script>
    window.fetch = async () => ({
        ok: true,
        json: async () => ({
            html: '<div id="root" data-hot-component data-hot-snapshot="next" data-hot-checksum="ok" data-hot-action="/hot"><button id="done">Done</button></div>',
            snapshot: { payload: 'next', checksum: 'ok' }
        })
    });
    window.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => document.getElementById('run')?.click(), 50);
    });
    </script>
    <script type="module" src="file://${app}"></script>
</body>
</html>`);

try {
    const result = spawnSync(browser, [
        '--headless',
        '--disable-gpu',
        '--no-sandbox',
        '--allow-file-access-from-files',
        '--virtual-time-budget=2000',
        '--dump-dom',
        `file://${html}`,
    ], { encoding: 'utf8' });

    if (result.status !== 0) {
        console.error(result.stderr || result.stdout);
        process.exit(result.status ?? 1);
    }

    if (!result.stdout.includes('id="done"')) {
        console.error(result.stdout);
        console.error('Hotfire browser smoke failed: morphed DOM was not observed.');
        process.exit(1);
    }

    console.log('Hotfire browser smoke passed.');
} finally {
    rmSync(dir, { recursive: true, force: true });
}
