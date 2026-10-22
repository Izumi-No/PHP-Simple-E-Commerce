/**
 * Wiring helper for stateful Alpine components.
 *
 * A custom element that needs two-way binding declares an internal scope and
 * exposes it through `x-modelable`. Consumers then either:
 *
 *   <z-checkbox x-data="{ agree: false }" x-model="agree"></z-checkbox>
 *   <z-checkbox model="form.agree"></z-checkbox>
 *
 * `model` is sugar: it gets rewritten to `x-model` before Alpine boots.
 */
export interface ModelBindingOptions {
	/** Alpine data expression used when the consumer didn't author one. */
	data: string;
	/** Property exposed via `x-modelable` (defaults to `value`). */
	property?: string;
	/** Attribute name consumers can use as `x-model` sugar. */
	modelAttribute?: string;
}

export function setupModelBinding(
	el: HTMLElement,
	options: ModelBindingOptions,
): void {
	const property = options.property ?? "value";
	const modelAttribute = options.modelAttribute ?? "model";

	if (!el.hasAttribute("x-data")) {
		el.setAttribute("x-data", options.data);
	}

	el.setAttribute("x-modelable", property);

	if (!el.hasAttribute("x-model")) {
		const model = el.getAttribute(modelAttribute);
		if (model) el.setAttribute("x-model", model);
	}
}

/** Read the current value of a bindable element's Alpine scope. */
export function readScopeValue(el: HTMLElement, property = "value"): unknown {
	return (el as unknown as Record<string, unknown>)[property];
}
