<div
    x-data
    class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 max-w-sm w-full pointer-events-none"
    aria-live="polite"
>
    <template x-for="toast in $store.toast.toasts" :key="toast.id">
        <div
            x-transition:enter="transform ease-out duration-300 transition"
            x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="pointer-events-auto flex items-start gap-3 w-full rounded-xl border bg-white p-4 shadow-xl text-foreground text-sm"
            :class="{
                'border-emerald-200 bg-emerald-50/50': toast.type === 'success',
                'border-rose-200 bg-rose-50/50': toast.type === 'error',
                'border-amber-200 bg-amber-50/50': toast.type === 'warning',
                'border-border bg-white': toast.type === 'info'
            }"
        >
            <!-- Icon -->
            <div class="shrink-0 mt-0.5">
                <template x-if="toast.type === 'success'">
                    <span class="inline-flex p-1 rounded-full bg-emerald-100 text-emerald-700">
                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                    </span>
                </template>
                <template x-if="toast.type === 'error'">
                    <span class="inline-flex p-1 rounded-full bg-rose-100 text-rose-700">
                        <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                    </span>
                </template>
                <template x-if="toast.type === 'warning'">
                    <span class="inline-flex p-1 rounded-full bg-amber-100 text-amber-700">
                        <i data-lucide="alert-circle" class="w-3.5 h-3.5"></i>
                    </span>
                </template>
                <template x-if="toast.type === 'info'">
                    <span class="inline-flex p-1 rounded-full bg-secondary text-foreground">
                        <i data-lucide="info" class="w-3.5 h-3.5"></i>
                    </span>
                </template>
            </div>

            <!-- Content -->
            <div class="flex-1">
                <p class="text-xs font-medium text-foreground" x-text="toast.message"></p>
            </div>

            <!-- Dismiss -->
            <button
                type="button"
                @click="$store.toast.remove(toast.id)"
                class="shrink-0 rounded-lg p-1 text-muted-foreground hover:text-foreground hover:bg-muted transition-colors"
            >
                <i data-lucide="x" class="w-3.5 h-3.5"></i>
            </button>
        </div>
    </template>
</div>
