import type { IThemeStore, Theme } from "../../domain/ports/theme-store";

const STORAGE_KEY = "doceiro:theme";
const root = () => document.documentElement;

/**
 * LocalStorage-backed theme adapter. Applies `.dark` on <html> so Tailwind's
 * `@custom-variant dark` kicks in.
 */
class LocalStorageThemeStore implements IThemeStore {
	private theme: Theme;

	constructor() {
		this.theme = this.read();
		this.apply();
	}

	get current(): Theme {
		return this.theme;
	}

	toggle(): Theme {
		this.set(this.theme === "dark" ? "light" : "dark");
		return this.theme;
	}

	set(theme: Theme): void {
		this.theme = theme;
		try {
			localStorage.setItem(STORAGE_KEY, theme);
		} catch {
			/* private mode */
		}
		this.apply();
	}

	private read(): Theme {
		try {
			const saved = localStorage.getItem(STORAGE_KEY) as Theme | null;
			if (saved === "dark" || saved === "light") return saved;
		} catch {
			/* ignore */
		}
		return window.matchMedia?.("(prefers-color-scheme: dark)").matches
			? "dark"
			: "light";
	}

	private apply(): void {
		root().classList.toggle("dark", this.theme === "dark");
	}
}

export const themeStore: IThemeStore = new LocalStorageThemeStore();
