import { BaseElement, defineElement } from "../../../lib/base";
import { icon } from "../../../lib/icons";
import { cn } from "../../../lib/utils";

const CONTROL =
	"peer size-4 shrink-0 rounded-[4px] border border-input shadow-xs outline-none transition-shadow focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:border-primary data-[state=checked]:bg-primary data-[state=checked]:text-primary-foreground";

export class Checkbox extends BaseElement {
	static observedAttributes = ["checked", "disabled", "model", "x-model"];

	private control: HTMLButtonElement | null = null;

	protected render(): void {
		this.innerHTML = `
			<button type="button" role="checkbox" data-slot="checkbox"
				:aria-checked="value ? 'true' : 'false'"
				:data-state="value ? 'checked' : 'unchecked'"
				@click="value = !value">
				<span class="pointer-events-none grid place-content-center text-current">
					${icon("check", { class: "size-3.5", strokeWidth: 3 })}
				</span>
				<span data-content class="contents"></span>
			</button>`;
		this.control = this.querySelector("button");
		if (this.control) {
			this.control.querySelector("svg")?.setAttribute("x-show", "value");
		}
		this.syncAttributes();
	}

	protected syncAttributes(): void {
		const control = this.control;
		if (!control) return;

		if (!this.hasAttribute("x-data")) {
			const initial = this.hasAttribute("checked")
				? "{ value: true }"
				: "{ value: false }";
			this.setAttribute("x-data", initial);
		}
		this.setAttribute("x-modelable", "value");
		if (!this.hasAttribute("x-model")) {
			const model = this.getAttribute("model");
			if (model) this.setAttribute("x-model", model);
		}

		control.disabled = this.hasAttribute("disabled");
		control.className = cn(CONTROL, this.getAttribute("class"));
	}
}

defineElement("z-checkbox", Checkbox);
