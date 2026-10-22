import { BaseElement, defineElement } from "../../../lib/base";
import { icon } from "../../../lib/icons";
import { escapeHtml, jsExpr } from "../../../lib/utils";

interface ItemMeta {
	value: string;
	trigger: string;
	nodes: Node[];
}

export class Accordion extends BaseElement {
	static observedAttributes = [
		"type",
		"value",
		"model",
		"x-model",
		"collapsible",
	];

	private items: ItemMeta[] = [];

	private get multiple(): boolean {
		return this.getAttribute("type") === "multiple";
	}

	protected render(): void {
		const nodes = Array.from(
			this.querySelectorAll<HTMLElement>(":scope > z-accordion-item"),
		);
		this.items = nodes.map((item, index) => ({
			value: item.getAttribute("value") ?? `item-${index + 1}`,
			trigger: item.getAttribute("trigger") ?? `Item ${index + 1}`,
			nodes: Array.from(item.childNodes),
		}));

		const body = this.items
			.map((item) => {
				const expr = jsExpr(item.value);
				const state = this.multiple
					? `values.includes(${expr})`
					: `value === ${expr}`;
				const toggle = this.multiple
					? `values.includes(${expr}) ? values = values.filter((v) => v !== ${expr}) : values.push(${expr})`
					: this.hasAttribute("collapsible")
						? `value = value === ${expr} ? null : ${expr}`
						: `value = ${expr}`;

				return `<div data-slot="accordion-item" class="border-b last:border-b-0">
					<h3 class="flex">
						<button type="button" data-slot="accordion-trigger"
							:data-state="${state} ? 'open' : 'closed'"
							:aria-expanded="${state}"
							@click="${toggle}"
							class="flex flex-1 items-start justify-between gap-4 rounded-md py-4 text-left text-sm font-medium transition-all outline-none hover:underline focus-visible:ring-[3px] focus-visible:ring-ring/50 disabled:pointer-events-none disabled:opacity-50">
							${escapeHtml(item.trigger)}
							<span data-slot="accordion-chevron" :data-state="${state} ? 'open' : 'closed'" class="pointer-events-none transition-transform duration-200 data-[state=open]:rotate-180">
								${icon("chevron-down", { class: "size-4" })}
							</span>
						</button>
					</h3>
					<div data-slot="accordion-content" x-show="${state}" class="overflow-hidden text-sm pb-4 pt-0">
						<div data-panel="${escapeHtml(item.value)}"></div>
					</div>
				</div>`;
			})
			.join("");

		this.innerHTML = `<div data-slot="accordion" class="w-full">${body}</div>`;

		for (const item of this.items) {
			const panel = this.querySelector(
				`[data-panel="${cssEscape(item.value)}"]`,
			);
			panel?.append(...item.nodes);
		}

		this.syncAttributes();
	}

	protected syncAttributes(): void {
		if (!this.hasAttribute("x-data")) {
			if (this.multiple) {
				const initial = this.getAttribute("value");
				const values = initial ? initial.split(",").map((v) => v.trim()) : [];
				this.setAttribute("x-data", `{ values: ${JSON.stringify(values)} }`);
				// values as array — see comment above: JSON array inside setAttribute is safe
			} else {
				const initial = this.getAttribute("value");
				this.setAttribute(
					"x-data",
					`{ value: ${initial ? jsExpr(initial) : "null"} }`,
				);
			}
		}
		this.setAttribute("x-modelable", this.multiple ? "values" : "value");
		if (!this.hasAttribute("x-model")) {
			const model = this.getAttribute("model");
			if (model) this.setAttribute("x-model", model);
		}
	}
}

function cssEscape(value: string): string {
	return value.replace(/["\\]/g, "\\$&");
}

defineElement("z-accordion", Accordion);
