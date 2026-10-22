import Alpine from "alpinejs";

export { Alpine };

let started = false;

/**
 * `Alpine.start()` must only run once, and only once every component module has
 * been imported (components register their element / `Alpine.data` /
 * `Alpine.store` at import time). This wrapper centralises that contract and,
 * after boot, sweeps for any element that ended up un-initialised (e.g. nodes
 * written into the DOM while Alpine was still importing).
 */
export function startAlpine(): void {
	if (started) return;
	started = true;
	window.Alpine = Alpine;
	Alpine.start();

	// Any `z-*` element still undefined after boot means a component module
	// failed to load or was not imported — surface the tag name.
	const missing = new Set<string>();
	document.querySelectorAll("*").forEach((el) => {
		const name = el.tagName.toLowerCase();
		if (name.startsWith("z-") && !customElements.get(name)) {
			missing.add(name);
		}
	});

	if (missing.size > 0) {
		console.warn(
			"[zui] undefined custom elements in the DOM (component not imported?)",
			[...missing].join(", "),
		);
	}
}

export function isAlpineStarted(): boolean {
	return started;
}

/**
 * Components rendered *after* Alpine booted (e.g. added dynamically) need
 * `initTree` called manually. Components rendered before boot are picked up by
 * the initial `Alpine.start()` walk instead. This helper decides which path to
 * take so we never double-initialize.
 */
export function initAlpineTree(root: HTMLElement): void {
	if (isAlpineStarted()) Alpine.initTree(root);
}

/** Alpine data is only registered at import time, before `start()`. */
export function registerAlpineData(
	name: string,
	factory: (...args: unknown[]) => object,
): void {
	Alpine.data(name, factory as never);
}

export function registerAlpineStore(name: string, value: object): void {
	Alpine.store(name, value as never);
}

/** Imperatively write to a host element's Alpine scope (open dialogs, etc.). */
export function setScopeValue(
	host: HTMLElement,
	key: string,
	value: unknown,
): void {
	const data = (
		Alpine as unknown as {
			$data?: (el: HTMLElement) => Record<string, unknown> | undefined;
		}
	).$data?.(host);
	if (data) data[key] = value;
}
