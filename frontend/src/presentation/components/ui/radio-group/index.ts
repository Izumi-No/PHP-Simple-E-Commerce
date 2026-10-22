import { BaseElement, defineElement } from "../../../lib/base";
import { cn, jsExpr } from "../../../lib/utils";

const RADIO_CLASSES =
	"aspect-square size-4 shrink-0 rounded-full border border-input shadow-xs outline-none transition-[color,box-shadow] focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:border-primary data-[state=checked]:text-primary";

export class RadioGroup extends BaseElement {
	static observedAttributes = ["model", "x-model", "value"];

	protected render(): void {
		this.innerHTML = `<div role="radiogroup" data-slot="radio-group" class="grid gap-3"><span data-content class="contents"></span></div>`;
		this.syncAttributes();
	}

	protected syncAttributes(): void {
		if (!this.hasAttribute("x-data")) {
			const initial = this.getAttribute("value");
			const expr = initial
				? `{ value: ${jsExpr(initial)} }`
				: "{ value: null }";
			this.setAttribute("x-data", expr);
		}
		this.setAttribute("x-modelable", "value");
		if (!this.hasAttribute("x-model")) {
			const model = this.getAttribute("model");
			if (model) this.setAttribute("x-model", model);
		}

		this.mergeClasses("[data-slot=radio-group]", cn("grid gap-3", ""));
	}
}

export class Radio extends BaseElement {
	static observedAttributes = ["value", "disabled"];

	private control: HTMLButtonElement | null = null;

	protected render(): void {
		const value = this.getAttribute("value") ?? "";
		const expr = jsExpr(value);

		this.innerHTML = `
			<label class="flex items-center gap-2 text-sm">
				<button type="button" role="radio" data-slot="radio"
					:aria-checked="value === ${expr} ? 'true' : 'false'"
					:data-state="value === ${expr} ? 'checked' : 'unchecked'"
					@click="value = ${expr}">
					<span class="pointer-events-none grid place-content-center">
						<span class="size-2 rounded-full bg-current" x-show="value === ${expr}"></span>
					</span>
				</button>
				<span data-content class="contents"></span>
			</label>`;
		this.control = this.querySelector("button");
		this.syncAttributes();
	}

	protected syncAttributes(): void {
		const control = this.control;
		if (!control) return;
		control.disabled = this.hasAttribute("disabled");
		control.className = cn(RADIO_CLASSES, this.getAttribute("class"));
	}
}

defineElement("z-radio-group", RadioGroup);
defineElement("z-radio", Radio);
