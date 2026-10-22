import { BaseElement, defineElement } from "../../../lib/base";
import { cn, escapeHtml, formatCurrency } from "../../../lib/utils";

export class Price extends BaseElement {
	static observedAttributes = ["value", "compare-at", "currency"];

	protected render(): void {
		this.innerHTML = `<span data-slot="price" class="inline-flex items-baseline gap-2"></span>`;
		this.syncAttributes();
	}

	protected syncAttributes(): void {
		const root = this.querySelector<HTMLElement>("[data-slot=price]");
		if (!root) return;

		const value = Number(this.getAttribute("value") ?? 0);
		const compare = this.getAttribute("compare-at");
		const currency = this.getAttribute("currency") ?? "BRL";
		const formatted = formatCurrency(value, currency);

		let html = `<span class="text-base font-semibold">${escapeHtml(formatted)}</span>`;

		if (compare) {
			const compareValue = Number(compare);
			const discount = Math.round((1 - value / compareValue) * 100);
			html += `<span class="text-sm text-muted-foreground line-through">${escapeHtml(
				formatCurrency(compareValue, currency),
			)}</span>`;
			if (discount > 0 && discount < 100) {
				html += `<span class="rounded-md bg-destructive/10 px-1.5 py-0.5 text-xs font-medium text-destructive">-${discount}%</span>`;
			}
		}

		root.innerHTML = html;
		root.className = cn(
			"inline-flex items-baseline gap-2",
			this.getAttribute("class"),
		);
	}
}

defineElement("z-price", Price);
