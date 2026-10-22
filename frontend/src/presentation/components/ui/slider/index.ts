import { BaseElement, defineElement } from "../../../lib/base";
import { cn } from "../../../lib/utils";

export const SLIDER_CLASSES =
	"h-1.5 w-full cursor-pointer appearance-none rounded-full bg-secondary accent-primary outline-none disabled:pointer-events-none disabled:opacity-50";

export class Slider extends BaseElement {
	static observedAttributes = [
		"min",
		"max",
		"step",
		"value",
		"disabled",
		"model",
		"x-model",
	];

	private input: HTMLInputElement | null = null;

	protected render(): void {
		this.innerHTML = `<input type="range" data-slot="slider" />`;
		this.input = this.querySelector("input");
		if (this.input) {
			this.transferModel(this.input);
		}
		this.syncAttributes();
	}

	protected syncAttributes(): void {
		const input = this.input;
		if (!input) return;

		input.min = this.getAttribute("min") ?? "0";
		input.max = this.getAttribute("max") ?? "100";
		input.step = this.getAttribute("step") ?? "1";

		if (!input.hasAttribute("x-model")) {
			input.value = this.getAttribute("value") ?? input.min;
		}

		input.disabled = this.hasAttribute("disabled");
		input.className = cn(SLIDER_CLASSES, this.getAttribute("class"));
	}
}

defineElement("z-slider", Slider);
