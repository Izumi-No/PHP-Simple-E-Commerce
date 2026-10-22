import { BaseElement, defineElement } from "../../../lib/base";
import { cn } from "../../../lib/utils";

export class Separator extends BaseElement {
	static observedAttributes = ["orientation"];

	protected render(): void {
		this.innerHTML = `<div data-slot="separator" role="separator"></div>`;
		this.syncAttributes();
	}

	protected syncAttributes(): void {
		const vertical = this.getAttribute("orientation") === "vertical";
		const el = this.querySelector<HTMLElement>("[data-slot=separator]");
		if (!el) return;

		el.setAttribute("aria-orientation", vertical ? "vertical" : "horizontal");
		el.className = cn(
			"shrink-0 bg-border",
			vertical ? "h-full w-px" : "h-px w-full",
			this.getAttribute("class"),
		);
	}
}

defineElement("z-separator", Separator);
