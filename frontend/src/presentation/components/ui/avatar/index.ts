import { BaseElement, defineElement } from "../../../lib/base";
import { cn, escapeHtml } from "../../../lib/utils";

const BASE = "relative flex size-8 shrink-0 overflow-hidden rounded-full";

export class Avatar extends BaseElement {
	static observedAttributes = ["src", "alt", "fallback"];

	protected render(): void {
		const src = this.getAttribute("src");
		const alt = this.getAttribute("alt") ?? "";
		const fallback = this.getAttribute("fallback") ?? initials(alt);

		const inner = src
			? `<img data-slot="avatar-image" src="${escapeHtml(src)}" alt="${escapeHtml(
					alt,
				)}" class="aspect-square size-full object-cover" />`
			: `<span data-slot="avatar-fallback" class="flex size-full items-center justify-center rounded-full bg-muted text-xs font-medium">${escapeHtml(
					fallback,
				)}</span>`;

		this.innerHTML = `<span data-slot="avatar" class="ok">${inner}<span data-content class="hidden"></span></span>`;

		const img = this.querySelector("img");
		img?.addEventListener("error", () => {
			img.replaceWith(fallbackNode(fallback));
		});

		this.syncAttributes();
	}

	protected syncAttributes(): void {
		const root = this.querySelector<HTMLElement>("[data-slot=avatar]");
		if (root) root.className = cn(BASE, this.getAttribute("class"));
	}
}

function fallbackNode(text: string): HTMLElement {
	const span = document.createElement("span");
	span.setAttribute("data-slot", "avatar-fallback");
	span.className =
		"flex size-full items-center justify-center rounded-full bg-muted text-xs font-medium";
	span.textContent = text;
	return span;
}

function initials(value: string): string {
	return value
		.split(/\s+/)
		.filter(Boolean)
		.slice(0, 2)
		.map((part) => part[0]?.toUpperCase() ?? "")
		.join("");
}

defineElement("z-avatar", Avatar);
