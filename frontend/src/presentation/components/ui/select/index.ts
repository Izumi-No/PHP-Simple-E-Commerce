import { BaseElement, defineElement } from "../../../lib/base";
import { icon } from "../../../lib/icons";
import { cn } from "../../../lib/utils";

export const SELECT_CLASSES =
	"h-9 w-full min-w-0 appearance-none rounded-md border border-input bg-transparent px-3 py-2 pr-8 text-sm shadow-xs outline-none transition-[color,box-shadow] disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]";

export class Select extends BaseElement {
	static observedAttributes = [
		"name",
		"disabled",
		"required",
		"multiple",
		"placeholder",
		"model",
		"x-model",
	];

	private select: HTMLSelectElement | null = null;

	protected render(): void {
		this.innerHTML = `
			<div class="relative">
				<select data-slot="select"></select>
				<span class="pointer-events-none absolute top-1/2 right-2.5 -translate-y-1/2 text-muted-foreground">
					${icon("chevron-down", { class: "size-4 opacity-50" })}
				</span>
			</div>`;
		this.select = this.querySelector("select");
		if (this.select) this.transferModel(this.select);
		this.syncAttributes();
	}

	protected projectContent(): void {
		const select = this.select ?? this.querySelector("select");
		if (!select) return;

		const placeholder = this.getAttribute("placeholder");
		if (placeholder && !this.hasAttribute("multiple")) {
			const option = document.createElement("option");
			option.value = "";
			option.textContent = placeholder;
			option.disabled = true;
			option.selected = true;
			option.hidden = true;
			select.append(option);
		}

		select.append(...this.originalContent);
	}

	protected syncAttributes(): void {
		const select = this.select;
		if (!select) return;

		for (const name of ["name"]) {
			const value = this.getAttribute(name);
			if (value !== null) select.setAttribute(name, value);
		}

		for (const boolean of ["disabled", "required", "multiple"]) {
			if (this.hasAttribute(boolean)) select.setAttribute(boolean, "");
			else select.removeAttribute(boolean);
		}

		select.className = cn(SELECT_CLASSES, this.getAttribute("class"));
	}
}

defineElement("z-select", Select);
