import { BaseElement, defineElement } from "../../../lib/base";
import { icon } from "../../../lib/icons";
import { clamp } from "../../../lib/utils";

const BUTTON =
	"grid size-8 place-items-center text-muted-foreground transition-colors hover:text-foreground disabled:pointer-events-none disabled:opacity-50";

export class QuantityInput extends BaseElement {
	static observedAttributes = [
		"value",
		"min",
		"max",
		"disabled",
		"model",
		"x-model",
	];

	protected render(): void {
		this.innerHTML = `
			<div data-slot="quantity-input" class="inline-flex items-center rounded-md border shadow-xs">
				<button type="button" data-action="decrement" class="${BUTTON}" aria-label="Diminuir">
					${icon("minus", { class: "size-4" })}
				</button>
				<input type="number" data-slot="quantity-value" x-model.number="value"
					class="h-8 w-12 border-x bg-transparent text-center text-sm outline-none [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none" />
				<button type="button" data-action="increment" class="${BUTTON}" aria-label="Aumentar">
					${icon("plus", { class: "size-4" })}
				</button>
			</div>`;
		this.bind();
		this.syncAttributes();
	}

	private get min(): number {
		return Number(this.getAttribute("min") ?? 1);
	}

	private get max(): number {
		return Number(this.getAttribute("max") ?? 999);
	}

	protected syncAttributes(): void {
		const initial = Number(this.getAttribute("value") ?? this.min);

		if (!this.hasAttribute("x-data")) {
			this.setAttribute(
				"x-data",
				`{ value: ${initial}, min: ${this.min}, max: ${this.max} }`,
			);
		}
		this.setAttribute("x-modelable", "value");
		if (!this.hasAttribute("x-model")) {
			const model = this.getAttribute("model");
			if (model) this.setAttribute("x-model", model);
		}

		const input = this.querySelector<HTMLInputElement>("input");
		if (input) {
			input.min = String(this.min);
			input.max = String(this.max);
			input.disabled = this.hasAttribute("disabled");
		}
		for (const button of this.querySelectorAll<HTMLButtonElement>("button")) {
			button.disabled = this.hasAttribute("disabled");
		}
	}

	private bind(): void {
		const decrement = this.querySelector<HTMLButtonElement>(
			'[data-action="decrement"]',
		);
		const increment = this.querySelector<HTMLButtonElement>(
			'[data-action="increment"]',
		);

		const write = (next: number): void => {
			const value = clamp(next, this.min, this.max);
			this.setAttribute("value", String(value));
			this.dispatchEvent(
				new CustomEvent("change", { detail: { value }, bubbles: true }),
			);
		};

		decrement?.addEventListener("click", () => write(this.current() - 1));
		increment?.addEventListener("click", () => write(this.current() + 1));
	}

	private current(): number {
		const value = Number(this.getAttribute("value") ?? this.min);
		return Number.isNaN(value) ? this.min : value;
	}
}

defineElement("z-quantity-input", QuantityInput);
