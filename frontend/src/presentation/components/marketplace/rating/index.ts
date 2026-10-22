import { BaseElement, defineElement } from "../../../lib/base";
import { escapeHtml } from "../../../lib/utils";

function star(filled: boolean): string {
	return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="14" height="14"
		fill="${filled ? "currentColor" : "none"}" stroke="currentColor" stroke-width="2"
		stroke-linecap="round" stroke-linejoin="round" class="shrink-0"
		aria-hidden="true"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.12 2.12 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.12 2.12 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.12 2.12 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.12 2.12 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.12 2.12 0 0 0 1.597-1.16z"/></svg>`;
}

export class Rating extends BaseElement {
	static observedAttributes = ["value", "max", "show-value", "count"];

	protected render(): void {
		this.innerHTML = `<span data-slot="rating" class="inline-flex items-center gap-1"></span>`;
		this.syncAttributes();
	}

	protected syncAttributes(): void {
		const root = this.querySelector<HTMLElement>("[data-slot=rating]");
		if (!root) return;

		const value = Number(this.getAttribute("value") ?? 0);
		const max = Number(this.getAttribute("max") ?? 5);
		const rounded = Math.round(value);

		let html = `<span class="inline-flex items-center gap-0.5 text-amber-500" role="img" aria-label="${escapeHtml(
			`${value} de ${max}`,
		)}">`;
		for (let i = 1; i <= max; i += 1) html += star(i <= rounded);
		html += "</span>";

		if (this.hasAttribute("show-value")) {
			html += `<span class="text-xs font-medium">${value.toFixed(1)}</span>`;
		}

		const count = this.getAttribute("count");
		if (count) {
			html += `<span class="text-xs text-muted-foreground">(${escapeHtml(count)})</span>`;
		}

		root.innerHTML = html;
	}
}

defineElement("z-rating", Rating);
