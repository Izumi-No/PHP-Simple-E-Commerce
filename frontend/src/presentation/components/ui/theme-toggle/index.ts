import { themeStore } from "../../../../infrastructure/store/theme";
import { BaseElement, defineElement } from "../../../lib/base";

const SUN = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>`;

const MOON = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>`;

/** Dark-mode switch. Uses the theme store so it persists in localStorage. */
export class ThemeToggle extends BaseElement {
	protected render(): void {
		this.innerHTML = `<button type="button" data-slot="theme-toggle"
			aria-label="Alternar tema"
			class="grid size-9 place-items-center rounded-full border border-border bg-card text-muted-foreground transition-colors hover:text-primary"></button>`;
		this.syncAttributes();
		this.querySelector("button")?.addEventListener("click", () => {
			themeStore.toggle();
			this.sync();
		});
	}

	protected syncAttributes(): void {
		this.sync();
	}

	private sync(): void {
		const btn = this.querySelector("button");
		if (!btn) return;
		btn.innerHTML = themeStore.current === "dark" ? MOON : SUN;
		btn.title = themeStore.current === "dark" ? "modo claro" : "modo escuro";
	}
}

defineElement("z-theme-toggle", ThemeToggle);
