import { BaseElement, defineElement } from "../../../lib/base";

const TABLE_CLASSES = "w-full caption-bottom text-sm";
const SELECTORS: Record<string, string> = {
	thead: "[&_tr]:border-b",
	tbody: "[&_tr:last-child]:border-0",
	tfoot: "border-t bg-muted/50 font-medium [&>tr]:last:border-b-0",
	tr: "border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted",
	th: "h-10 px-2 text-left align-middle font-medium whitespace-nowrap text-muted-foreground",
	td: "p-2 align-middle",
	caption: "mt-4 text-sm text-muted-foreground",
};

export class Table extends BaseElement {
	protected render(): void {
		this.innerHTML = `<div data-slot="table-container" class="relative w-full overflow-x-auto"></div>`;
	}

	protected projectContent(): void {
		const container = this.querySelector<HTMLElement>(
			"[data-slot=table-container]",
		);
		if (!container) return;
		container.append(...this.originalContent);

		const table = container.querySelector("table");
		if (!table) return;
		table.classList.add(...TABLE_CLASSES.split(" "));
		for (const [tag, classes] of Object.entries(SELECTORS)) {
			for (const el of table.querySelectorAll(tag)) {
				el.classList.add(...classes.split(" "));
			}
		}
	}
}

defineElement("z-table", Table);
