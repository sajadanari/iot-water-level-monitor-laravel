<div class="mb-8" x-data="{ loading: false }">
    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">LED Toggle</h2>

    <x-card>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">

            {{-- DEVICE SELECTOR --}}
            <div class="flex items-center space-x-2">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Select Device:
                </label>
                <select wire:model="device_id"
                    class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm p-2">
                    <option value="">-- Choose Device --</option>
                    @foreach($devices ?? [] as $device)
                        <option value="{{ $device }}">{{ $device }}</option>
                    @endforeach
                </select>
            </div>

            {{-- LED STATUS DISPLAY --}}
            <div class="flex items-center space-x-3">
                {{-- <div class="flex items-center">
                    <div class="relative w-4 h-4 mr-2">
                        <div class="absolute inset-0 rounded-full transition-all duration-300 {{
                            $ledStatus === true ? 'bg-green-400 shadow-green-400/50 shadow-lg'
                            : 'bg-red-400 shadow-red-400/50 shadow-lg' }}" >
                        </div>
                    </div>
                    <span class="text-sm font-medium {{ $ledStatus === true ? 'text-green-500' : 'text-red-500' }}">
                        <span>{{ $ledStatus === true ? 'ON' : 'OFF' }}</span>
                    </span>
                </div> --}}

                {{-- TOGGLE BUTTON --}}
                <button
                    @click="loading = true; $wire.toggleLed().then(() => loading = false)"
                    :disabled="!@entangle('device_id').live || loading"
                    class="inline-flex items-center px-4 py-2 rounded-md text-sm font-medium border transition-all duration-200
                        focus:outline-none focus:ring-2 focus:ring-offset-2
                        disabled:opacity-50 disabled:cursor-not-allowed
                        bg-indigo-600 text-white hover:bg-indigo-700 dark:bg-indigo-700 dark:hover:bg-indigo-600 border-indigo-600 dark:border-indigo-500">
                    <template x-if="loading">
                        <svg class="animate-spin h-4 w-4 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                    </template>

                    <span>Turn {{ $ledStatus ? 'Off' : 'On' }} LED</span>
                </button>
            </div>
        </div>

        {{-- MESSAGE SECTION --}}
        @if ($message)
            <div class="mt-4">
                <div class="px-4 py-2 rounded-md text-sm font-medium
                    @if (Str::contains(strtolower($message), 'on'))
                        bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300
                    @elseif (Str::contains(strtolower($message), 'off'))
                        bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300
                    @else
                        bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300
                    @endif">
                    {{ $message }}
                </div>
            </div>
        @endif

        {{-- Optional: Help text --}}
        <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
            <p>
                Use this panel to send LED toggle commands to the selected device.
                The command will be queued and processed by the device shortly.
            </p>
        </div>
    </x-card>
</div>
