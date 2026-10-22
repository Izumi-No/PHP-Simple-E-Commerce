import { setScopeValue } from "../../../lib/alpine";
import { BaseElement, defineElement } from "../../../lib/base";
import { icon } from "../../../lib/icons";
import { escapeHtml } from "../../../lib/utils";
import { buttonVariants } from "../button/index";

const SIDES: Record<string, string> = {
	right:
		"inset-y-0 right-0 h-full w-3/4 border-l sm:max-w-sm data-[state=open]:slide-in-from-right",
	left: "inset-y-0 left-0 h-full w-3/4 border-r sm:max-w-sm",
	top: "inset-x-0 top-0 h-auto border-b",
	bottom: "inset-x-0 bottom-0 h-auto border-t",
};

const BASE =
	"fixed z-50 flex flex-col gap-4 bg-background p-6 shadow-lg transition ease-in-out";

export class Sheet extends BaseElement {
	static observedAttributes = ["trigger", "open", "side", "model", "x-model"];

	protected render(): void {
		const trigger = this.getAttribute("trigger");
		const triggerHtml = trigger
			? `<button type="button" data-slot="sheet-trigger" @click="open = true" class="${buttonVariants({ variant: "outline" })}">${escapeHtml(trigger)}</button>`
			: "";

		this.innerHTML = `
			${triggerHtml}
			<div data-slot="sheet-overlay" x-show="open" x-transition.opacity.duration.200ms @click="open = false" class="fixed inset-0 z-50 bg-black/50"></div>
			<div data-slot="sheet-content" x-show="open" x-transition.opacity.duration.200ms
				role="dialog" aria-modal="true" @click.stop class="${BASE}">
				<span data-content class="contents"></span>
				<button type="button" data-slot="sheet-close" @click="open = false"
					class="absolute top-4 right-4 rounded-xs opacity-70 transition-opacity hover:opacity-100 focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none">
					${icon("x", { class: "size-4" })}<span class="sr-only">Fechar</span>
				</button>
			</div>`;

		this.syncAttributes();
	}

	protected syncAttributes(): void {
		if (!this.hasAttribute("x-data")) {
			this.setAttribute("x-data", `{ open: ${this.hasAttribute("open")} }`);
		}
		if (!this.hasAttribute("x-model")) {
			const model = this.getAttribute("model");
			if (model) this.setAttribute("x-model", model);
		}
		if (this.hasAttribute("x-model")) this.setAttribute("x-modelable", "open");
		this.setAttribute("x-on:keydown.escape.window", "open = false");

		const side = this.getAttribute("side") ?? "right";
		const panel = this.querySelector<HTMLElement>("[data-slot=sheet-content]");
		if (panel) {
			panel.className = `${BASE} ${SIDES[side] ?? SIDES.right}`;
		}
	}

	show(): void {
		setScopeValue(this, "open", true);
	}

	hide(): void {
		setScopeValue(this, "open", false);
	}
}

defineElement("z-sheet", Sheet);
