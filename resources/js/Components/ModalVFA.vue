<template>
	<Teleport to="body">
		<Transition name="modal-fade">
			<div
				v-if="open"
				class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 px-4 py-10 backdrop-blur-sm"
				role="dialog"
				aria-modal="true"
				@click.self="handleBackdrop"
			>
				<Transition name="modal-scale">
					<section
						class="relative z-10 w-full max-w-md origin-center rounded-3xl border border-slate-200 bg-white p-8 shadow-2xl ring-1 ring-slate-900/5 transition-all dark:border-slate-700 dark:bg-slate-900"
					>
						<button
							v-if="dismissible"
							class="absolute right-4 top-4 text-slate-400 transition-colors hover:text-slate-600 focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2 focus-visible:outline-none"
							type="button"
							aria-label="Cerrar"
							@click="handleClose"
						>
							<svg
								class="size-5"
								xmlns="http://www.w3.org/2000/svg"
								fill="none"
								viewBox="0 0 24 24"
								stroke-width="1.5"
								stroke="currentColor"
							>
								<path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6m0 12L6 6" />
							</svg>
						</button>

						<div class="mx-auto flex size-16 items-center justify-center rounded-full border border-emerald-200 bg-emerald-50 text-emerald-600 shadow-inner">
							<svg
								class="size-7"
								xmlns="http://www.w3.org/2000/svg"
								fill="none"
								viewBox="0 0 24 24"
								stroke-width="1.5"
								stroke="currentColor"
							>
								<path
									stroke-linecap="round"
									stroke-linejoin="round"
									d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
								/>
							</svg>
						</div>

						<header class="mt-6 space-y-2 text-center">
							<h2 class="text-2xl font-semibold tracking-tight text-slate-900 dark:text-white">
								{{ title }}
							</h2>
							<p class="text-sm text-slate-500 dark:text-slate-300">
								{{ subtitle }}
								<span class="font-medium text-slate-900 dark:text-white">{{ email }}</span>
							</p>
						</header>

						<form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
							<div
								class="relative grid grid-cols-6 gap-3"
								@paste="handlePaste"
							>
								<div
									v-for="(digit, index) in digits"
									:key="index"
									class="group"
								>
									<Input
										:ref="(el) => setInputRef(el, index)"
										:model-value="digit"
										type="text"
										inputmode="numeric"
										pattern="[0-9]*"
										maxlength="1"
										autocomplete="one-time-code"
										class="h-14 w-full rounded-2xl border-2 border-slate-200 text-center text-2xl font-semibold tracking-[0.35em] transition-all focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100 group-hover:shadow-lg dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:focus:border-emerald-400 dark:focus:ring-emerald-900/40"
										@update:modelValue="(value) => handleDigitInput(String(value ?? ''), index)"
										@focus="handleFocus(index)"
										@keydown="handleKeydown($event, index)"
									/>
								</div>

								<div
									v-if="loading"
									class="pointer-events-none absolute inset-0 rounded-2xl bg-white/40 backdrop-blur-sm dark:bg-slate-900/40"
								/>
							</div>

							<Transition name="slide-fade">
								<p
									v-if="error"
									class="rounded-2xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-600 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400"
									role="alert"
									aria-live="assertive"
								>
									{{ error }}
								</p>
							</Transition>

							<Button
								type="submit"
								class="w-full rounded-2xl py-6 text-base font-semibold"
								:disabled="!canSubmit"
							>
								<svg
									v-if="loading"
									class="size-5 animate-spin text-white"
									xmlns="http://www.w3.org/2000/svg"
									fill="none"
									viewBox="0 0 24 24"
								>
									<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
									<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4l3-3-3-3v4A8 8 0 1 0 4 12Z" />
								</svg>
								<span v-else>Confirmar código</span>
							</Button>
						</form>

						<footer class="mt-6 flex flex-col items-center gap-3">
							<button
								type="button"
								class="text-sm font-medium text-emerald-600 transition-colors hover:text-emerald-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 dark:text-emerald-400 dark:hover:text-emerald-300"
								:disabled="loading"
								@click="handleResend"
							>
								¿No recibiste tu código? Reenviar
							</button>
							<p class="text-xs text-slate-400 dark:text-slate-500">
								El código caduca en unos minutos. Ingresa solo números.
							</p>
						</footer>
					</section>
				</Transition>
			</div>
		</Transition>
	</Teleport>
</template>

<script setup lang="ts">
import type { ComponentPublicInstance } from "vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { computed, nextTick, onBeforeUnmount, ref, watch } from "vue";
import type { VerifyAuth } from "@/types/auth";

const CODE_LENGTH = 6;

const props = withDefaults(
	defineProps<{
		open: boolean;
		email: string;
		loading?: boolean;
		error?: string | null;
		title?: string;
		subtitle?: string;
		dismissible?: boolean;
	}>(),
	{
		loading: false,
		error: null,
		title: "Verifica tu identidad",
		subtitle: "Ingresa el código de 6 dígitos enviado a",
		dismissible: true,
	},
);

const emit = defineEmits<{
	(e: "update:open", value: boolean): void;
	(e: "submit", payload: VerifyAuth): void;
	(e: "resend", payload: { email: string }): void;
}>();

const digits = ref<string[]>(Array(CODE_LENGTH).fill(""));
const inputRefs = ref<(HTMLInputElement | null)[]>(Array(CODE_LENGTH).fill(null));

const code = computed(() => digits.value.join(""));
const canSubmit = computed(() => code.value.length === CODE_LENGTH && !props.loading);
const open = computed(() => props.open);
const dismissible = computed(() => props.dismissible);

const focusInput = (index: number) => {
	const el = inputRefs.value[index];
	if (el) {
		el.focus();
		el.select();
	}
};

const resetDigits = () => {
	digits.value = Array(CODE_LENGTH).fill("");
};

const setInputRef = (el: Element | ComponentPublicInstance | null, index: number) => {
	if (!el) {
		inputRefs.value[index] = null;
		return;
	}

	if (el instanceof HTMLInputElement) {
		inputRefs.value[index] = el;
	} else {
		const possibleInput = (el as ComponentPublicInstance & { $el: Element }).$el;
		inputRefs.value[index] = possibleInput instanceof HTMLInputElement ? possibleInput : null;
	}
};

const syncFocusForward = (startIndex: number) => {
	const firstEmpty = digits.value.findIndex((value, idx) => idx >= startIndex && value === "");
	const targetIndex = firstEmpty === -1 ? Math.min(CODE_LENGTH - 1, startIndex) : firstEmpty;
	nextTick(() => focusInput(targetIndex));
};

const handleDigitInput = (rawValue: string, index: number) => {
	const sanitized = rawValue.replace(/\D/g, "");

	if (!sanitized) {
		digits.value[index] = "";
		return;
	}

	const chars = sanitized.slice(0, CODE_LENGTH - index).split("");
	const nextDigits = [...digits.value];

	for (let offset = 0; offset < chars.length; offset += 1) {
		const currentIndex = index + offset;
		if (currentIndex >= CODE_LENGTH) break;
		const nextChar = chars[offset] ?? "";
		nextDigits[currentIndex] = nextChar;
	}

	digits.value = nextDigits;

	const nextIndex = Math.min(index + chars.length, CODE_LENGTH - 1);
	syncFocusForward(nextIndex);
};

const handleKeydown = (event: KeyboardEvent, index: number) => {
	if (event.key === "Backspace") {
		event.preventDefault();
		if (digits.value[index]) {
			digits.value[index] = "";
			syncFocusForward(index);
			return;
		}

		if (index > 0) {
			digits.value[index - 1] = "";
			nextTick(() => focusInput(index - 1));
		}
	}

	if (event.key === "ArrowLeft" && index > 0) {
		event.preventDefault();
		focusInput(index - 1);
	}

	if (event.key === "ArrowRight" && index < CODE_LENGTH - 1) {
		event.preventDefault();
		focusInput(index + 1);
	}
};

const handlePaste = (event: ClipboardEvent) => {
	const pasted = event.clipboardData?.getData("text") ?? "";
	const sanitized = pasted.replace(/\D/g, "").slice(0, CODE_LENGTH);
	if (!sanitized) return;

	event.preventDefault();
	digits.value = sanitized.split("").concat(Array(CODE_LENGTH).fill(""))
		.slice(0, CODE_LENGTH);
	syncFocusForward(CODE_LENGTH - 1);
};

const handleSubmit = () => {
	if (!canSubmit.value) return;
    const credentials : VerifyAuth = { correo: props.email, code: code.value };
	emit("submit", credentials);
};

const handleResend = () => {
	if (props.loading) return;
	emit("resend", { email: props.email });
};

const handleFocus = (index: number) => {
	nextTick(() => focusInput(index));
};

const handleClose = () => {
	if (!props.dismissible) return;
	if (props.loading) return;
	emit("update:open", false);
};

const handleBackdrop = () => {
	if (!props.dismissible) return;
	handleClose();
};

const handleEscape = (event: KeyboardEvent) => {
	if (!props.dismissible) return;
	if (event.key === "Escape" && props.open) {
		handleClose();
	}
};

const attachEscapeListener = () => {
	window.addEventListener("keydown", handleEscape);
};

const detachEscapeListener = () => {
	window.removeEventListener("keydown", handleEscape);
};

watch(open, (isOpen) => {
	if (isOpen) {
		resetDigits();
		if (props.dismissible) {
			attachEscapeListener();
		} else {
			detachEscapeListener();
		}
		nextTick(() => focusInput(0));
	} else {
		detachEscapeListener();
	}
});

watch(
	() => props.dismissible,
	(canDismiss) => {
		if (!props.open) return;
		if (canDismiss) {
			attachEscapeListener();
		} else {
			detachEscapeListener();
		}
	},
);

onBeforeUnmount(() => {
	detachEscapeListener();
});
</script>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
	transition: opacity 180ms ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
	opacity: 0;
}

.modal-scale-enter-active,
.modal-scale-leave-active {
	transition: transform 200ms ease, opacity 200ms ease;
}

.modal-scale-enter-from,
.modal-scale-leave-to {
	transform: scale(0.94);
	opacity: 0;
}

.slide-fade-enter-active {
	transition: all 220ms ease;
}

.slide-fade-enter-from {
	opacity: 0;
	transform: translateY(-6px);
}

.slide-fade-leave-active {
	transition: all 160ms ease;
}

.slide-fade-leave-to {
	opacity: 0;
	transform: translateY(-4px);
}
</style>
