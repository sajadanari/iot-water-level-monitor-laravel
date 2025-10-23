<div>
    @if($alerts->isNotEmpty())
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Active Alerts</h2>
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Threshold:</label>
                    <select wire:model.live="alertThreshold" class="text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md">
                        <option value="all">All</option>
                        <option value="critical">Critical</option>
                        <option value="moderate">Moderate</option>
                    </select>
                </div>
            </div>

            <div class="space-y-3">
                @foreach($alerts as $alert)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow border-l-4 p-4"
                        x-bind:class="getAlertBorderColor('{{ $alert["severity"] }}')"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center"
                                        x-bind:class="getAlertIconBg('{{ $alert["severity"] }}')"
                                    >
                                        @if($alert['severity'] === 'critical')
                                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                            </svg>
                                        @elseif($alert['severity'] === 'moderate')
                                            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                                        <span class="font-bold">{{ $alert->message }}</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $alert->waterLevel->device_id }}
                                        </span>
                                    </h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $alert['created_at']->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    x-bind:class="getAlertBadgeColor('{{ $alert["severity"] }}')"
                                >
                                    {{ ucfirst($alert['severity']) }}
                                </span>
                                <button wire:click="dismissAlert({{ $alert['id'] }})" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
    function getAlertBorderColor(severity) {
        switch(severity) {
            case 'critical': return 'border-red-500';
            case 'moderate': return 'border-yellow-500';
            default: return 'border-blue-500';
        }
    }

    function getAlertIconBg(severity) {
        switch(severity) {
            case 'critical': return 'bg-red-100 dark:bg-red-900';
            case 'moderate': return 'bg-yellow-100 dark:bg-yellow-900';
            default: return 'bg-blue-100 dark:bg-blue-900';
        }
    }

    function getAlertBadgeColor(severity) {
        switch(severity) {
            case 'critical': return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
            case 'moderate': return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
            default: return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
        }
    }
</script>
@endpush('scripts')
