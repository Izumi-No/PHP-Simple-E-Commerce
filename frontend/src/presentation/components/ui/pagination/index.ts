import { BaseElement, defineElement } from "../../../lib/base";
import { icon } from "../../../lib/icons";
import { clamp, cn } from "../../../lib/utils";

export interface PageChangeDetail {
	page: number;
}

const ITEM =
	"inline-flex h-9 min-w-9 items-center justify-center rounded-md px-3 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:pointer-events-none disabled:opacity-50";

export class Pagination extends BaseElement {
	static observedAttributes = ["page", "total", "per-page"];

	private get pages(): number {
		const total = Number(this.getAttribute("total") ?? 0);
		const perPage = Number(this.getAttribute("per-page") ?? 10) || 10;
		return Math.max(1, Math.ceil(total / perPage));
	}

	private get page(): number {
		return clamp(Number(this.getAttribute("page") ?? 1), 1, this.pages);
	}

	protected render(): void {
		this.innerHTML = `<nav data-slot="pagination" aria-label="pagination" class="mx-auto flex w-full justify-center"></nav>`;
		this.build();
	}

	protected syncAttributes(): void {
		this.build();
	}

	private build(): void {
		const nav = this.querySelector<HTMLElement>("[data-slot=pagination]");
		if (!nav) return;

		const page = this.page;
		const pages = this.pages;
		const range = pageRange(page, pages);

		nav.innerHTML = `
			<ul class="flex flex-row items-center gap-1">
				<li>
					<button type="button" data-page="${page - 1}" ${page <= 1 ? "disabled" : ""}
						class="${cn(ITEM, "gap-1 pl-2.5 hover:bg-accent hover:text-accent-foreground")}">
						${icon("chevron-left", { class: "size-4" })}<span class="hidden sm:block">Anterior</span>
					</button>
				</li>
				${range
					.map((entry) =>
						entry === "..."
							? `<li><span class="${cn(ITEM, "pointer-events-none")}" aria-hidden="true">…</span></li>`
							: `<li>
									<button type="button" data-page="${entry}"
										aria-current="${entry === page ? "page" : "false"}"
										class="${cn(
											ITEM,
											entry === page
												? "border bg-background shadow-xs"
												: "hover:bg-accent hover:text-accent-foreground",
										)}">${entry}</button>
								</li>`,
					)
					.join("")}
				<li>
					<button type="button" data-page="${page + 1}" ${page >= pages ? "disabled" : ""}
						class="${cn(ITEM, "gap-1 pr-2.5 hover:bg-accent hover:text-accent-foreground")}">
						<span class="hidden sm:block">Próximo</span>${icon("chevron-right", { class: "size-4" })}
					</button>
				</li>
			</ul>`;

		for (const button of nav.querySelectorAll<HTMLButtonElement>(
			"[data-page]",
		)) {
			button.addEventListener("click", () => {
				const next = clamp(Number(button.dataset.page), 1, this.pages);
				if (next === this.page) return;
				this.setAttribute("page", String(next));
				this.dispatchEvent(
					new CustomEvent<PageChangeDetail>("page-change", {
						detail: { page: next },
						bubbles: true,
					}),
				);
			});
		}
	}
}

function pageRange(page: number, pages: number): Array<number | "..."> {
	if (pages <= 7) {
		return Array.from({ length: pages }, (_, i) => i + 1);
	}
	const result: Array<number | "..."> = [1];
	const start = Math.max(2, page - 1);
	const end = Math.min(pages - 1, page + 1);
	if (start > 2) result.push("...");
	for (let i = start; i <= end; i += 1) result.push(i);
	if (end < pages - 1) result.push("...");
	result.push(pages);
	return result;
}

defineElement("z-pagination", Pagination);
