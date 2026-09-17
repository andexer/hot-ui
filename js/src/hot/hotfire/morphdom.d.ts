declare module 'morphdom' {
    interface MorphOptions {
        onNodeAdded?: (node: Node) => void;
        onNodeDiscarded?: (node: Node) => void;
        onBeforeElUpdated?: (fromEl: HTMLElement, toEl: HTMLElement) => boolean | void;
    }

    function morphdom(fromEl: HTMLElement, toEl: HTMLElement, options?: MorphOptions): HTMLElement;

    export default morphdom;
}