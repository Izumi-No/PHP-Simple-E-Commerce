import { BaseElement, defineElement } from "../../../lib/base";
import { cn } from "../../../lib/utils";

const TRACK =
	"peer inline-flex h-5 w-9 shrink-0 items-center rounded-full border-2 border-transparent shadow-xs transition-colors outline-none focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50 data-[state=checked]:bg-primary data-[state=unchecked]:bg-input";

const THUMB =
	"pointer-events-none block size-4 rounded-full bg-background shadow-sm ring-0 transition-transform data-[state=checked]:translate-x-4 data-[state=unchecked]:translate-x-0";

export class Switch extends BaseElement {
	static observedAttributes = ["checked", "disabled", "model", "x-model"];

	private control: HTMLButtonElement | null = null;

	protected render(): void {
		this.innerHTML = `
			<button type="button" role="switch" data-slot="switch"
				:aria-checked="value ? 'true' : 'false'"
				:data-state="value ? 'checked' : 'unchecked'"
				@click="value = !value">
				<span class="${THUMB}" :data-state="value ? 'checked' : 'unchecked'"></span>
			</button>`;
		this.control = this.querySelector("button");
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
		control.className = cn(TRACK, this.getAttribute("class"));
	}
}

defineElement("z-switch", Switch);
