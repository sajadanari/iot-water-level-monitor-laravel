<div>
    <x-section title="Water Level Monitor Dashboard" description="Monitor your water tank levels in real-time">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Water Level Card -->
            <x-card title="Current Water Level" description="Real-time water level reading">
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">
                        {{ $waterLevel }}%
                    </div>
                    <div class="mt-2">
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                                 style="width: {{ $waterLevel }}%"></div>
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Status Card -->
            <x-card title="Tank Status" description="Current tank status">
                <div class="text-center">
                    <div class="text-2xl font-semibold {{ $status === 'Good' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $status }}
                    </div>
                    <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Last updated: {{ $lastUpdated }}
                    </div>
                </div>
            </x-card>

            <!-- Actions Card -->
            <x-card title="Quick Actions" description="Manage your water monitoring">
                <div class="space-y-2">
                    <x-button wire:click="refreshData" variant="primary" size="sm" class="w-full">
                        Refresh Data
                    </x-button>
                    <x-button wire:click="toggleAlerts" variant="outline" size="sm" class="w-full">
                        {{ $alertsEnabled ? 'Disable' : 'Enable' }} Alerts
                    </x-button>
                </div>
            </x-card>
        </div>

        <!-- Recent Readings -->
        <div class="mt-8">
            <x-card title="Recent Readings" description="Latest water level measurements">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Time
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Level
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($recentReadings as $reading)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $reading['time'] }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $reading['level'] }}%
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $reading['status'] === 'Good' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                                            {{ $reading['status'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    </x-section>

    @if($showAlert)
        <x-alert variant="warning" class="mt-4">
            <strong>Low Water Level Alert!</strong> Your water tank is running low. Consider refilling soon.
        </x-alert>
    @endif
</div>
