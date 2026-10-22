import { BaseElement, defineElement } from "../../../lib/base";
import { cn, escapeHtml } from "../../../lib/utils";

const POSITIONS: Record<string, string> = {
	top: "bottom-full left-1/2 mb-2 -translate-x-1/2",
	bottom: "top-full left-1/2 mt-2 -translate-x-1/2",
	left: "top-1/2 right-full mr-2 -translate-y-1/2",
	right: "top-1/2 left-full ml-2 -translate-y-1/2",
};

export class Tooltip extends BaseElement {
	static observedAttributes = ["content", "side"];

	protected render(): void {
		const side = this.getAttribute("side") ?? "top";
		const content = this.getAttribute("content") ?? "";

		this.innerHTML = `
			<span data-slot="tooltip" class="group relative inline-flex">
				<span data-content class="contents"></span>
				<span data-slot="tooltip-content" role="tooltip"
					class="pointer-events-none absolute z-50 w-fit rounded-md bg-primary px-3 py-1.5 text-xs text-primary-foreground opacity-0 shadow-md transition-opacity group-hover:opacity-100 group-focus-within:opacity-100 ${POSITIONS[side] ?? POSITIONS.top}">
					${escapeHtml(content)}
				</span>
			</span>`;

		this.syncAttributes();
	}

	protected syncAttributes(): void {
		const host = this.querySelector<HTMLElement>("[data-slot=tooltip]");
		if (host)
			host.className = cn(
				"group relative inline-flex",
				this.getAttribute("class"),
			);
	}
}

defineElement("z-tooltip", Tooltip);
