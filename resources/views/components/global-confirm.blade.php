<div x-data="{
        isOpen: false,
        message: '',
        title: 'Confirm Action',
        action: null,
        confirm() {
            if (this.action) {
                this.action();
            }
            this.isOpen = false;
        },
        cancel() {
            this.isOpen = false;
        }
    }"
    @open-confirm.window="
        message = $event.detail.message || 'Are you sure?';
        title = $event.detail.title || 'Confirm Action';
        action = $event.detail.action;
        isOpen = true;
    "
    x-show="isOpen"
    x-cloak
    style="display: none;"
    class="relative z-[9999]"
    aria-labelledby="modal-title" role="dialog" aria-modal="true">
    
    <!-- Backdrop -->
    <div x-show="isOpen"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <!-- Modal Panel -->
            <div x-show="isOpen"
                 @click.away="cancel()"
                 @keydown.escape.window="cancel()"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-gray-100 dark:border-gray-700">
                
                <div class="bg-white dark:bg-gray-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30 sm:mx-0 sm:h-10 sm:w-10 border border-amber-200 dark:border-amber-800/50">
                            <i class="bi bi-exclamation-triangle text-amber-600 dark:text-amber-500 text-lg"></i>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-gray-100" id="modal-title" x-text="title"></h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400" x-html="message.replace(/\n/g, '<br>')"></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 dark:bg-gray-800/50 px-4 py-4 sm:flex sm:flex-row-reverse sm:px-6 border-t border-gray-100 dark:border-gray-700/50">
                    <button type="button" @click="confirm()" class="inline-flex w-full justify-center rounded-xl bg-hotel-dark hover:bg-black text-white px-4 py-2.5 text-sm font-semibold shadow-sm sm:ml-3 sm:w-auto transition-colors">
                        Confirm
                    </button>
                    <button type="button" @click="cancel()" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white dark:bg-gray-700 px-4 py-2.5 text-sm font-semibold text-gray-900 dark:text-gray-200 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 sm:mt-0 sm:w-auto transition-colors">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
