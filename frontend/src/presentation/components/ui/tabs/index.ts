import { BaseElement, defineElement } from "../../../lib/base";
import { escapeHtml, jsExpr } from "../../../lib/utils";

const LIST =
	"inline-flex h-9 w-fit items-center justify-center rounded-lg bg-muted p-1 text-muted-foreground";

const TRIGGER =
	"inline-flex flex-1 items-center justify-center gap-1.5 rounded-md px-3 py-1 text-sm font-medium whitespace-nowrap transition-[color,box-shadow] outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow-sm";

const CONTENT = "flex-1 text-sm outline-none";

interface TabMeta {
	value: string;
	label: string;
	nodes: Node[];
}

export class Tabs extends BaseElement {
	static observedAttributes = ["value", "model", "x-model"];

	private tabs: TabMeta[] = [];

	protected render(): void {
		const nodes = Array.from(
			this.querySelectorAll<HTMLElement>(":scope > z-tab"),
		);
		this.tabs = nodes.map((tab, index) => ({
			value: tab.getAttribute("value") ?? `tab-${index + 1}`,
			label:
				tab.getAttribute("label") ??
				tab.getAttribute("value") ??
				`Tab ${index + 1}`,
			nodes: Array.from(tab.childNodes),
		}));

		const triggers = this.tabs
			.map((tab) => {
				const expr = jsExpr(tab.value);
				return `<button type="button" role="tab" data-slot="tabs-trigger"
					:data-state="value === ${expr} ? 'active' : 'inactive'"
					:aria-selected="value === ${expr}"
					@click="value = ${expr}; $dispatch('change', { value: ${expr} })"
					class="${TRIGGER}">${escapeHtml(tab.label)}</button>`;
			})
			.join("");

		const panels = this.tabs
			.map((tab) => {
				const expr = jsExpr(tab.value);
				return `<div role="tabpanel" data-slot="tabs-content" data-panel="${escapeHtml(
					tab.value,
				)}" x-show="value === ${expr}" class="${CONTENT}"></div>`;
			})
			.join("");

		this.innerHTML = `
			<div data-slot="tabs" class="flex flex-col gap-2">
				<div role="tablist" aria-orientation="horizontal" class="${LIST}">${triggers}</div>
				${panels}
			</div>`;

		for (const tab of this.tabs) {
			const panel = this.querySelector(
				`[data-panel="${cssEscape(tab.value)}"]`,
			);
			panel?.append(...tab.nodes);
		}

		this.syncAttributes();
	}

	protected syncAttributes(): void {
		if (!this.hasAttribute("x-data")) {
			const initial = this.getAttribute("value") ?? this.tabs[0]?.value ?? "";
			this.setAttribute("x-data", `{ value: ${jsExpr(initial)} }`);
		}
		this.setAttribute("x-modelable", "value");
		if (!this.hasAttribute("x-model")) {
			const model = this.getAttribute("model");
			if (model) this.setAttribute("x-model", model);
		}
	}
}

function cssEscape(value: string): string {
	return value.replace(/["\\]/g, "\\$&");
}

defineElement("z-tabs", Tabs);
