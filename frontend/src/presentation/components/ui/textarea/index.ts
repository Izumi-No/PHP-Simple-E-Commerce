import { BaseElement, defineElement } from "../../../lib/base";
import { cn } from "../../../lib/utils";

export const TEXTAREA_CLASSES =
	"flex min-h-16 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs transition-[color,box-shadow] outline-none placeholder:text-muted-foreground disabled:cursor-not-allowed disabled:opacity-50 focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] aria-invalid:border-destructive aria-invalid:ring-destructive/20";

export class Textarea extends BaseElement {
	static observedAttributes = [
		"placeholder",
		"name",
		"rows",
		"maxlength",
		"minlength",
		"value",
		"disabled",
		"required",
		"readonly",
	];

	private textarea: HTMLTextAreaElement | null = null;

	protected render(): void {
		this.innerHTML = `<textarea data-slot="textarea"></textarea>`;
		this.textarea = this.querySelector("textarea");
		if (this.textarea) this.transferModel(this.textarea);
		this.syncAttributes();
	}

	protected syncAttributes(): void {
		const textarea = this.textarea;
		if (!textarea) return;

		for (const name of [
			"placeholder",
			"name",
			"rows",
			"maxlength",
			"minlength",
		]) {
			const value = this.getAttribute(name);
			if (value !== null) textarea.setAttribute(name, value);
		}

		for (const boolean of ["disabled", "required", "readonly"]) {
			if (this.hasAttribute(boolean)) textarea.setAttribute(boolean, "");
			else textarea.removeAttribute(boolean);
		}

		if (!textarea.hasAttribute("x-model")) {
			const value = this.getAttribute("value");
			if (value !== null) textarea.value = value;
		}

		textarea.className = cn(TEXTAREA_CLASSES, this.getAttribute("class"));

		const projected = textarea.querySelector("[data-content]");
		if (projected) projected.replaceWith(...projected.childNodes);
	}
}

defineElement("z-textarea", Textarea);
