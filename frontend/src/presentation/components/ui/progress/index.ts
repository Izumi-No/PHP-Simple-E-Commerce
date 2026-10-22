import { BaseElement, defineElement } from "../../../lib/base";
import { clamp, cn } from "../../../lib/utils";

export class Progress extends BaseElement {
	static observedAttributes = ["value", "max"];

	protected render(): void {
		this.innerHTML = `
			<div data-slot="progress" role="progressbar" class="relative h-2 w-full overflow-hidden rounded-full bg-primary/20">
				<div data-slot="progress-indicator" class="h-full w-full flex-1 bg-primary transition-all"></div>
			</div>`;
		this.syncAttributes();
	}

	protected syncAttributes(): void {
		const value = Number(this.getAttribute("value") ?? 0);
		const max = Number(this.getAttribute("max") ?? 100) || 100;
		const percent = clamp((value / max) * 100, 0, 100);

		const root = this.querySelector<HTMLElement>("[data-slot=progress]");
		const indicator = this.querySelector<HTMLElement>(
			"[data-slot=progress-indicator]",
		);

		if (root) {
			root.setAttribute("aria-valuemin", "0");
			root.setAttribute("aria-valuemax", String(max));
			root.setAttribute("aria-valuenow", String(value));
			root.className = cn(
				"relative h-2 w-full overflow-hidden rounded-full bg-primary/20",
				this.getAttribute("class"),
			);
		}
		if (indicator) indicator.style.transform = `translateX(-${100 - percent}%)`;
	}
}

defineElement("z-progress", Progress);
