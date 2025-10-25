<div>
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-row items-center">

                {{-- LOGO --}}
                <div class="invisible mr-0 w-0 dark:visible dark:mr-4 dark:w-auto">
                    <img class="" width="100pt" height="100pt" src="{{ asset('defaults/WL-Logo-Light-Color.svg') }}" alt="WaterLevelMonitor">
                </div>

                <div class="dark:invisible dark:mr-0 dark:w-0 mr-4">
                    <img class="" width="100pt" height="100pt" src="{{ asset('defaults/WL-Logo-Main-Color.svg') }}" alt="WaterLevelMonitor">
                </div>

                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Water Level Monitor</h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Real-time monitoring of water tank levels across all devices
                    </p>

                    {{-- Show Last Refresh --}}
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Last Refresh: {{ now()->format('H:i:s') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-4 sm:mt-0 flex flex-wrap gap-4">
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Device:</label>
                    <select wire:model.live="selectedDevice" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm p-2">
                        <option value="all">All Devices</option>
                        @foreach($devices as $device)
                            <option value="{{ $device }}">{{ $device }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Period:</label>
                    <select wire:model.live="timeRange" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm p-2">
                        <option value="1">Last Hour</option>
                        <option value="6">Last 6 Hours</option>
                        <option value="24">Last 24 Hours</option>
                        <option value="72">Last 3 Days</option>
                        <option value="168">Last Week</option>
                    </select>
                </div>

                <div class="flex items-center space-x-2">
                    <button wire:click="refreshData" class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh
                    </button>

                    <button wire:click="toggleAutoRefresh" class="inline-flex items-center px-3 py-2 border rounded-md text-sm font-medium {{ $autoRefresh ? 'bg-green-100 text-green-800 border-green-300 dark:bg-green-900 dark:text-green-200 dark:border-green-700' : 'bg-gray-100 text-gray-800 border-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600' }}">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Auto Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <x-card>
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Readings</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($statistics['total_readings']) }}</p>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Average Level</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $statistics['avg_level'] }}cm</p>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-100 dark:bg-yellow-900 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Battery Level</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $statistics['avg_battery'] }}%</p>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900 rounded-md flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Temperature</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $statistics['avg_temperature'] }}°C</p>
                </div>
            </div>
        </x-card>
    </div>

    {{-- ADDED x-data="{}" to enable Alpine context for calling JS helper functions --}}
    <div class="mb-8" x-data="{}">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Device Status</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($latestReadings as $device)
                <x-card>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ $device['device_id'] }}</h3>
                        <div class="flex items-center space-x-2">
                            <div class="w-2 h-2 rounded-full {{ $device['is_online'] ? 'bg-green-400' : 'bg-red-400' }}"></div>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $device['is_online'] ? 'Online' : 'Offline' }}
                            </span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-1">
                            <span>Water Level</span>
                            <span>{{ $device['level_percentage'] }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                            {{-- Changed :class to x-bind:class for clarity. The x-data above makes the function accessible. --}}
                            <div class="h-3 rounded-full transition-all duration-300"
                                style="width: {{ $device['level_percentage'] }}%"
                                x-bind:class="getProgressBarColor({{ $device['level_percentage'] }})"></div>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $device['level_cm'] }}cm - {{ ucfirst($device['status']) }}</p>
                    </div>

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Battery:</span>
                            <span class="text-gray-900 dark:text-white">{{ $device['battery_level'] ?? 'N/A' }}%</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Temperature:</span>
                            <span class="text-gray-900 dark:text-white">{{ $device['temperature'] ?? 'N/A' }}°C</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Last Reading:</span>
                            <span class="text-gray-900 dark:text-white">{{ $device['last_reading'] }}</span>
                        </div>
                    </div>
                </x-card>
            @empty
                <div class="col-span-full">
                    <x-card>
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No devices found</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No water level data available for the selected criteria.</p>
                        </div>
                    </x-card>
                </div>
            @endforelse
        </div>
    </div>

    {{-- This section is a separate Livewire component and should manage its own Alpine context if needed --}}
    <livewire:water-level-alerts-component />

    <div class="mb-8">
        {{-- The water-level-chart component should be fixed as per our previous conversation --}}
        <livewire:water-level-chart
            :deviceId="$selectedDevice"
            :timeRange="$timeRange"
            wire:key="{{ $selectedDevice . '-' . $timeRange }}"
        />
    </div>

    {{-- ADDED x-data="{}" to enable Alpine context for calling JS helper functions for the badge color --}}
    <div class="mb-8" x-data="{}">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Recent Readings</h2>
        <x-card>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Device</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Level</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Battery</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Temperature</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Time</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($recentReadings as $reading)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $reading->device_id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $reading->level_cm }}cm
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{-- Changed :class to x-bind:class for clarity. The x-data above makes the function accessible. --}}
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        x-bind:class="getStatusBadgeColor({{ $this->calculatePercentage($reading->level_cm) }})">
                                        {{ ucfirst($this->getStatus($this->calculatePercentage($reading->level_cm))) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $reading->battery_level ?? 'N/A' }}%
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $reading->temperature ?? 'N/A' }}°C
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $reading->created_at->format('M j, Y g:i A') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No recent readings found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($recentReadings->hasPages())
                <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700">
                    {{ $recentReadings->links() }}
                </div>
            @endif
        </x-card>
    </div>

    <div>
        <livewire:led-controll-component :devices="$devices"/>
    </div>

    @if($autoRefresh)
        <script>
            setInterval(function() {
                @this.call('refreshData');
            }, {{ $refreshInterval * 1000 }});
        </script>
    @endif
</div>

{{-- scripts --}}
@push('scripts')
<script>
    // Helper methods for the component
    function getProgressBarColor(percentage) {
        if (percentage >= 80) return 'bg-green-500';
        if (percentage >= 60) return 'bg-blue-500';
        if (percentage >= 40) return 'bg-yellow-500';
        if (percentage >= 20) return 'bg-orange-500';
        return 'bg-red-500';
    }

    function getStatusBadgeColor(percentage) {
        if (percentage >= 80) return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
        if (percentage >= 60) return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200';
        if (percentage >= 40) return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
        if (percentage >= 20) return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200';
        return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
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
