<div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                Water Level Trends (Last {{ $timeRange }} Hours)
            </h3>

            <div class="flex items-center space-x-2">
                {{-- Corrected binding (no ".live") --}}
                {{-- <select wire:model.live="timeRange"
                        class="text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md">
                    <option value="1">1 Hour</option>
                    <option value="6">6 Hours</option>
                    <option value="24">24 Hours</option>
                    <option value="72">3 Days</option>
                    <option value="168">1 Week</option>
                </select> --}}
            </div>
        </div>

        <div class="h-64">
            <canvas
                wire:ignore
                x-data="{
                    chart: null,
                    livewireEventName: 'chart-updated-{{ $this->getId() }}',

                    init() {
                        const initialData = {{ Js::from($chartData) }};

                        this.chart = new Chart(this.$refs.canvas, {
                            type: 'line',
                            data: { datasets: initialData },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: { mode: 'index', intersect: false },
                                scales: {
                                    x: {
                                        type: 'time',
                                        time: { unit: 'hour' },
                                        title: { display: true, text: 'Time' }
                                    },
                                    y: {
                                        min: 0,
                                        max: 100,
                                        title: { display: true, text: 'Water Level (%)' }
                                    }
                                }
                            }
                        });

                        // Listen to Livewire updates
                        Livewire.on(this.livewireEventName, (event) => {
                            if (this.chart && event.chartData) {
                                this.chart.data.datasets = event.chartData;
                                this.chart.update();
                            }
                        });
                    }
                }"
                x-ref="canvas"
            ></canvas>
        </div>

        <div class="mt-4 text-xs text-gray-400 text-center">
            <p>Data points: {{ collect($chartData)->flatten(1)->filter(fn($item) => is_array($item) && isset($item['x']))->count() }}</p>
            <p>Devices: {{ count($chartData) }}</p>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.30.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment@1.0.1/dist/chartjs-adapter-moment.min.js"></script>
@endpush
