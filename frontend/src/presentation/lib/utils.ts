export type ClassValue =
	| string
	| number
	| null
	| undefined
	| false
	| ClassValue[]
	| Record<string, boolean | null | undefined>;

/**
 * Tiny `clsx` alternative. Kept dependency-free on purpose so the design
 * system ships with the bundle. If you later want tailwind-merge semantics,
 * swap the body of `cn` for `twMerge(clsx(...))`.
 */
export function cx(...inputs: ClassValue[]): string {
	const out: string[] = [];

	const walk = (value: ClassValue): void => {
		if (!value) return;

		if (typeof value === "string" || typeof value === "number") {
			out.push(String(value));
			return;
		}

		if (Array.isArray(value)) {
			for (const item of value) walk(item);
			return;
		}

		if (typeof value === "object") {
			for (const [key, enabled] of Object.entries(value)) {
				if (enabled) out.push(key);
			}
		}
	};

	for (const input of inputs) walk(input);

	return out.join(" ");
}

/** Composition helper used across every component. */
export function cn(...inputs: ClassValue[]): string {
	return cx(...inputs);
}

/**
 * Produce a single-quoted JS string literal safe to embed inside a
 * double-quoted HTML attribute (Alpine expressions inside `innerHTML`).
 */
export function jsExpr(value: string): string {
	return `'${value.replace(/\\/g, "\\\\").replace(/'/g, "\\'")}'`;
}

/** Escape a value before interpolating it into an HTML template string. */
export function escapeHtml(value: unknown): string {
	return String(value ?? "")
		.replace(/&/g, "&amp;")
		.replace(/</g, "&lt;")
		.replace(/>/g, "&gt;")
		.replace(/"/g, "&quot;")
		.replace(/'/g, "&#39;");
}

/** Format a number as BRL currency (marketplace default). */
export function formatCurrency(value: number, currency = "BRL"): string {
	return new Intl.NumberFormat("pt-BR", {
		style: "currency",
		currency,
	}).format(value);
}

export function clamp(value: number, min: number, max: number): number {
	return Math.min(Math.max(value, min), max);
}

let uid = 0;
/** Stable, collision-free id generator for aria wiring. */
export function uniqueId(prefix = "z"): string {
	uid += 1;
	return `${prefix}-${uid}`;
}
