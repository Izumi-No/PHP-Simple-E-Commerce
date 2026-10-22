export {};

declare global {
	/** Parcel env loader (inlined at build time). */
	const process: {
		env: Record<string, string | undefined>;
	};
}
