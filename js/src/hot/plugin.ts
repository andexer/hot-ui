import type { AlpineLike, DirectiveHandler, MagicHandler } from './types.js';

/**
 * Everything an island may touch, handed over once at registration time.
 * Islands never import Alpine directly — they receive it here (DIP), which
 * keeps them testable and makes the single loader the only composition point.
 */
export interface HotContext {
    /** The live Alpine engine (already started or about to start). */
    readonly alpine: AlpineLike;

    /**
     * Registers a directive/magic/data component immediately. Provided for
     * islands that contribute their OWN primitives beyond controllers.
     */
    directive(name: string, handler: DirectiveHandler): void;
    magic<T>(name: string, handler: MagicHandler<T>): void;
    data(name: string, factory: (config?: unknown) => unknown): void;
}

/** The one interface every island implements (ISP: one small method). */
export interface IslandPlugin {
    /** Registry name, mirrors the mirrored file name (e.g. "dropdown-menu"). */
    readonly name: string;
    register(context: HotContext): void;
}
