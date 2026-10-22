import { BaseElement, defineElement } from "../../../lib/base";
import { cn } from "../../../lib/utils";

export const INPUT_CLASSES =
	"flex h-9 w-full min-w-0 rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:border-destructive aria-invalid:ring-destructive/20";

const FORWARDED = [
	"type",
	"placeholder",
	"name",
	"min",
	"max",
	"step",
	"autocomplete",
	"inputmode",
	"pattern",
	"list",
	"accept",
];

export class Input extends BaseElement {
	static observedAttributes = [
		...FORWARDED,
		"value",
		"disabled",
		"required",
		"readonly",
	];

	private input: HTMLInputElement | null = null;

	protected render(): void {
		this.innerHTML = `<input data-slot="input" />`;
		this.input = this.querySelector("input");
		if (this.input) this.transferModel(this.input);
		this.syncAttributes();
	}

	protected syncAttributes(): void {
		const input = this.input;
		if (!input) return;

		for (const name of FORWARDED) {
			const value = this.getAttribute(name);
			if (value !== null) input.setAttribute(name, value);
		}

		for (const boolean of ["disabled", "required", "readonly"]) {
			if (this.hasAttribute(boolean)) input.setAttribute(boolean, "");
			else input.removeAttribute(boolean);
		}

		if (!input.hasAttribute("x-model")) {
			const value = this.getAttribute("value");
			if (value !== null) input.value = value;
		}

		input.className = cn(INPUT_CLASSES, this.getAttribute("class"));
	}
}

defineElement("z-input", Input);
