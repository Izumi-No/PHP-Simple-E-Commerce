import { Alpine, registerAlpineStore } from "../../../lib/alpine";
import { defineElement, StaticElement } from "../../../lib/base";
import { icon } from "../../../lib/icons";

export type ToastVariant = "default" | "destructive" | "success";

export interface ToastOptions {
	title?: string;
	description?: string;
	variant?: ToastVariant;
	/** Milliseconds before auto-dismiss. `0` keeps it until dismissed. */
	duration?: number;
}

interface ToastItem extends Required<Omit<ToastOptions, "duration">> {
	id: number;
}

let counter = 0;

const toastStore = {
	items: [] as ToastItem[],
	add(options: ToastOptions = {}): number {
		const id = ++counter;
		const item: ToastItem = {
			id,
			title: options.title ?? "",
			description: options.description ?? "",
			variant: options.variant ?? "default",
		};
		this.items.push(item);

		const duration = options.duration ?? 4000;
		if (duration > 0) {
			setTimeout(() => this.remove(id), duration);
		}
		return id;
	},
	remove(id: number): void {
		this.items = this.items.filter((item) => item.id !== id);
	},
};

registerAlpineStore("toasts", toastStore);

/** Imperative API — works from any event handler. */
export function toast(options: ToastOptions = {}): number {
	const store = Alpine.store("toasts") as typeof toastStore | undefined;
	return store ? store.add(options) : 0;
}

const VARIANTS: Record<ToastVariant, string> = {
	default: "border bg-background text-foreground",
	destructive: "border-destructive bg-destructive text-destructive-foreground",
	success: "border-success bg-success text-success-foreground",
};

export class Toaster extends StaticElement {
	constructor() {
		super(`
			<div data-slot="toaster"
				class="fixed top-4 right-4 z-[100] flex max-h-screen w-full flex-col gap-2 sm:max-w-[420px]"
				x-data>
				<template x-for="item in $store.toasts.items" :key="item.id">
					<div data-slot="toast" :data-variant="item.variant"
						x-transition:enter="transition ease-out duration-200"
						x-transition:enter-start="opacity-0 translate-x-4"
						:class="{
							'border bg-background text-foreground': item.variant === 'default',
							'border-destructive bg-destructive text-destructive-foreground': item.variant === 'destructive',
							'border-success bg-success text-success-foreground': item.variant === 'success'
						}"
						class="pointer-events-auto relative flex w-full items-start gap-3 overflow-hidden rounded-lg border p-4 shadow-lg">
						<div class="flex-1 grid gap-1">
							<p class="text-sm font-semibold" x-text="item.title"></p>
							<p class="text-sm opacity-90" x-text="item.description"></p>
						</div>
						<button type="button" @click="$store.toasts.remove(item.id)"
							class="shrink-0 rounded-md p-1 opacity-70 transition-opacity hover:opacity-100">
							${icon("x", { class: "size-4" })}<span class="sr-only">Fechar</span>
						</button>
					</div>
				</template>
			</div>
		`);
	}
}

export const toastVariants = VARIANTS;
defineElement("z-toaster", Toaster);
