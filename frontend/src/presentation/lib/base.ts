import { initAlpineTree } from "./alpine";
import { cn } from "./utils";

const CUSTOM_ELEMENT_NAME = /^[a-z][a-z0-9._-]*-[a-z0-9._-]+$/;

export interface DefineOptions extends ElementDefinitionOptions {
	/** Skip the `customElements.get` guard (used for re-registration in HMR). */
	force?: boolean;
}

/**
 * Crash-proof `customElements.define`.
 *
 * - Validates the tag name (must contain a hyphen).
 * - Idempotent (`customElements.get` check).
 * - Never throws: a broken component logs a warning instead of taking down
 *   every component registered after it.
 */
export function defineElement(
	name: string,
	ctor: CustomElementConstructor,
	options?: DefineOptions,
): boolean {
	const { force, ...definitionOptions } = options ?? {};

	if (!CUSTOM_ELEMENT_NAME.test(name)) {
		console.error(
			`[zui] "${name}" is not a valid custom element name (must contain a hyphen). Component skipped.`,
		);
		return false;
	}

	if (!force && customElements.get(name)) return true;

	try {
		customElements.define(name, ctor, definitionOptions);
		return true;
	} catch (error) {
		console.error(`[zui] failed to define <${name}>:`, error);
		return false;
	}
}

export interface BehaviorOptions {
	/** Classes appended to the element (merged with its existing classes). */
	classes?: string;
	/** Attributes applied to the element. `null` removes the attribute. */
	attrs?: Record<string, string | null>;
	/** Event listeners bound to the element. */
	on?: Record<string, EventListener>;
}

export interface BehaviorHandle {
	update(options: BehaviorOptions): void;
	destroy(): void;
}

/**
 * Applies classes/attributes/listeners to a (native or custom) element and
 * returns a handle so callers can update or tear it down. This is how the
 * "behaviour only" controls (button, input, badge…) stay tiny.
 */
export function applyBehavior(
	el: HTMLElement,
	options: BehaviorOptions,
): BehaviorHandle {
	let cleanup: Array<() => void> = [];

	const update = (next: BehaviorOptions): void => {
		for (const fn of cleanup) fn();
		cleanup = [];

		if (next.classes) {
			const classes = cn(next.classes).split(/\s+/).filter(Boolean) as string[];
			el.classList.add(...classes);
		}

		for (const [name, value] of Object.entries(next.attrs ?? {})) {
			if (value === null) el.removeAttribute(name);
			else el.setAttribute(name, value);
		}

		for (const [event, handler] of Object.entries(next.on ?? {})) {
			el.addEventListener(event, handler);
			cleanup.push(() => el.removeEventListener(event, handler));
		}
	};

	update(options);

	return {
		update,
		destroy: () => {
			for (const fn of cleanup) fn();
			cleanup = [];
		},
	};
}

/**
 * Base class for *rendering* components, light-DOM only (Alpine's `initTree`
 * does not pierce shadow roots and `x-on` relies on regular event bubbling).
 *
 * Guarantees:
 * - `connectedCallback` never throws: a rendering error produces a visible
 *   fallback with the error message instead of a blank page.
 * - Original children are projected into `[data-content]` (or handled by the
 *   subclass via `projectContent`).
 */
export abstract class BaseElement extends HTMLElement {
	static observedAttributes: string[] = [];

	protected initialized = false;
	protected originalContent: Node[] = [];

	connectedCallback(): void {
		if (this.initialized) return;
		this.initialized = true;

		try {
			this.originalContent = Array.from(this.childNodes);
			this.render();
			this.projectContent();
			this.syncAttributes();
			initAlpineTree(this);
			this.onConnected();
		} catch (error) {
			console.error(`[zui] <${this.tagName.toLowerCase()}> failed:`, error);
			this.textContent = `[${this.localName}] render error`;
			this.classList.add(
				...[
					"block",
					"rounded-md",
					"border",
					"border-destructive/50",
					"bg-destructive/10",
					"p-3",
					"text-xs",
					"text-destructive",
				],
			);
		}
	}

	/**
	 * Once rendered, a component stays rendered even if it's moved in the DOM
	 * (e.g. projected by a parent). `disconnectedCallback` intentionally does
	 * NOT reset `initialized` — otherwise moving a node would re-render it with
	 * its own already-rendered output as content, duplicating markup.
	 */
	disconnectedCallback(): void {
		this.onDisconnected();
	}

	attributeChangedCallback(
		name: string,
		oldValue: string | null,
		newValue: string | null,
	): void {
		if (oldValue === newValue || !this.initialized) return;
		this.syncAttributes();
		this.onAttributeChanged(name, oldValue, newValue);
	}

	/** Subclasses return the component markup. */
	protected abstract render(): void;

	/** Apply host attributes to the rendered markup. */
	protected syncAttributes(): void {}

	protected onConnected(): void {}
	protected onDisconnected(): void {}
	protected onAttributeChanged(
		_name: string,
		_oldValue: string | null,
		_newValue: string | null,
	): void {}

	/**
	 * Forward Alpine's two-way binding to a rendered native control.
	 * `x-model` authored on the host is moved to the control; `model="x"`
	 * is treated as sugar for `x-model="x"`.
	 */
	protected transferModel(target: HTMLElement): void {
		const authored = this.getAttribute("x-model");
		if (authored) {
			target.setAttribute("x-model", authored);
			this.removeAttribute("x-model");
			return;
		}
		const model = this.getAttribute("model");
		if (model) target.setAttribute("x-model", model);
	}

	/** Merge the author's `class` attribute into a rendered element. */
	protected mergeClasses(selector: string | HTMLElement, base: string): void {
		const target =
			typeof selector === "string"
				? this.querySelector<HTMLElement>(selector)
				: selector;
		if (!target) return;
		const classes = cn(base, this.getAttribute("class"))
			.split(/\s+/)
			.filter(Boolean) as string[];
		target.classList.add(...classes);
	}

	protected projectContent(): void {
		const target = this.querySelector<HTMLElement>("[data-content]");
		if (!target || this.originalContent.length === 0) return;
		target.replaceChildren(...this.originalContent);
	}
}

/**
 * Creates a lightweight element whose sole purpose is to apply utility classes
 * to itself while preserving the author's content (card parts, badge, etc.).
 */
export function createStyledElement(classes: string): CustomElementConstructor {
	return class extends HTMLElement {
		connectedCallback(): void {
			this.classList.add(...classes.split(/\s+/).filter(Boolean));
			initAlpineTree(this);
		}
	};
}

/** Simple autonomous element whose only job is to render a template. */
export abstract class StaticElement extends BaseElement {
	private template: string;

	constructor(template: string) {
		super();
		this.template = template;
	}

	protected render(): void {
		this.innerHTML = this.template;
	}
}
