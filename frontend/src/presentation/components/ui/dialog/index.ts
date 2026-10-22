import { setScopeValue } from "../../../lib/alpine";
import { BaseElement, defineElement } from "../../../lib/base";
import { icon } from "../../../lib/icons";
import { escapeHtml } from "../../../lib/utils";
import { buttonVariants } from "../button/index";

const OVERLAY = "fixed inset-0 z-50 bg-black/50";

const CONTENT =
	"fixed top-1/2 left-1/2 z-50 grid w-full max-w-[calc(100%-2rem)] -translate-x-1/2 -translate-y-1/2 gap-4 rounded-lg border bg-background p-6 shadow-lg duration-200 sm:max-w-lg";

export class Dialog extends BaseElement {
	static observedAttributes = ["trigger", "open", "model", "x-model"];

	protected render(): void {
		const trigger = this.getAttribute("trigger");
		const triggerHtml = trigger
			? `<button type="button" data-slot="dialog-trigger" @click="open = true" class="${buttonVariants()}">${escapeHtml(trigger)}</button>`
			: "";

		this.innerHTML = `
			${triggerHtml}
			<div data-slot="dialog-overlay" x-show="open" x-transition.opacity.duration.200ms @click="open = false" class="${OVERLAY}"></div>
			<div data-slot="dialog-content" x-show="open" x-transition.opacity.duration.200ms
				role="dialog" aria-modal="true" @click.stop class="${CONTENT}">
				<span data-content class="contents"></span>
				<button type="button" data-slot="dialog-close" @click="open = false"
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
	}

	show(): void {
		setScopeValue(this, "open", true);
	}

	hide(): void {
		setScopeValue(this, "open", false);
	}

	toggle(): void {
		setScopeValue(this, "open", !this.hasAttribute("open"));
	}
}

defineElement("z-dialog", Dialog);
