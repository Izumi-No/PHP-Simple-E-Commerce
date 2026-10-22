import { BaseElement, defineElement } from "../../../lib/base";
import { cn } from "../../../lib/utils";

export const LABEL_CLASSES =
	"flex items-center gap-2 text-sm leading-none font-medium select-none group-data-[disabled=true]:pointer-events-none group-data-[disabled=true]:opacity-50 peer-disabled:cursor-not-allowed peer-disabled:opacity-50";

export class Label extends BaseElement {
	static observedAttributes = ["for", "required"];

	private label: HTMLLabelElement | null = null;

	protected render(): void {
		this.innerHTML = `<label data-slot="label"><span data-content class="contents"></span></label>`;
		this.label = this.querySelector("label");
		this.syncAttributes();
	}

	protected syncAttributes(): void {
		const label = this.label;
		if (!label) return;

		const htmlFor = this.getAttribute("for");
		if (htmlFor) label.htmlFor = htmlFor;

		label.className = cn(LABEL_CLASSES, this.getAttribute("class"));
	}
}

defineElement("z-label", Label);
