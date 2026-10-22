export type Theme = "light" | "dark";

/** Driving port: persistent user preference. */
export interface IThemeStore {
	readonly current: Theme;
	toggle(): Theme;
	set(theme: Theme): void;
}
