@if(session()->has('toast'))
    @php
        $toast = session('toast');
    @endphp
    
    <div x-data="{
        show: true,
        message: '{{ addslashes($toast['message']) }}',
        type: '{{ $toast['type'] }}',
        init() {
            // Auto-hide after 5 seconds
            setTimeout(() => {
                this.show = false;
            }, 5000);
        }
    }" x-show="show" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="fixed inset-x-0 top-4 flex items-end justify-center px-4 py-6 pointer-events-none sm:p-6 sm:items-start sm:justify-end z-50">
        <div :class="{
                'bg-green-50 border-green-200': type === 'success',
                'bg-red-50 border-red-200': type === 'error',
                'bg-yellow-50 border-yellow-200': type === 'warning',
                'bg-blue-50 border-blue-200': type === 'info'
            }"
            class="max-w-sm w-full shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden border">
            <div class="p-4">
                <div class="flex items-start">
                    <!-- Icon -->
                    <div class="flex-shrink-0">
                        <svg x-show="type === 'success'" class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <svg x-show="type === 'error'" class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <svg x-show="type === 'warning'" class="h-5 w-5 text-yellow-400" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                        <svg x-show="type === 'info'" class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                    </div>

                    <!-- Message -->
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-sm font-medium text-gray-900" x-text="type.charAt(0).toUpperCase() + type.slice(1)">
                        </p>
                        <p class="mt-1 text-sm text-gray-500" x-text="message"></p>
                    </div>

                    <!-- Close button -->
                    <div class="ml-4 flex-shrink-0 flex">
                        <button @click="show = false"
                            class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Progress bar -->
            <div class="w-full h-1 bg-gray-200">
                <div x-show="show" x-transition:enter="transition-all ease-linear duration-5000"
                    x-transition:enter-start="w-full" x-transition:enter-end="w-0"
                    class="h-full transition-all duration-5000ms"
                    :class="{
                        'bg-green-500': type === 'success',
                        'bg-red-500': type === 'error',
                        'bg-yellow-500': type === 'warning',
                        'bg-blue-500': type === 'info'
                    }">
                </div>
            </div>
        </div>
    </div>
    
    {{-- Clear the session toast after displaying it --}}
    @php
        session()->forget('toast');
    @endphp
@endif