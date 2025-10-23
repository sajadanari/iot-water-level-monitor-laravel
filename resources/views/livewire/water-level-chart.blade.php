<div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
            {{-- Display the current time range --}}
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                Water Level Trends (Last {{ $timeRange }} Hours)
            </h3>
            <div class="flex items-center space-x-2">
                {{-- Dropdown linked to Livewire public property. The wire:model.live triggers updatedTimeRange() --}}
                <select wire:model.live="timeRange" class="text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md">
                    <option value="1">1 Hour</option>
                    <option value="6">6 Hours</option>
                    <option value="24">24 Hours</option>
                    <option value="72">3 Days</option>
                    <option value="168">1 Week</option>
                </select>
            </div>
        </div>
        
        {{-- Chart container --}}
        <div class="h-64">
            {{-- 
                x-data: Alpine.js data container.
                x-init: Function to initialize the Chart.js instance and set up the Livewire listener.
                wire:ignore: Crucial to prevent Livewire from destroying the Chart.js instance.
                x-ref="canvas": Reference for Chart.js to target the element.
            --}}
            <canvas 
                wire:ignore 
                x-data="{ 
                    chart: null,
                    // Unique Livewire event name based on the component's ID
                    livewireEventName: 'chart-updated-{{ $this->getId() }}',
                    
                    init() {
                        const initialData = {{ Js::from($chartData) }};
                        
                        this.chart = new Chart(this.$refs.canvas, {
                            type: 'line',
                            data: {
                                datasets: initialData,
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: {
                                    mode: 'index',
                                    intersect: false,
                                },
                                scales: {
                                    x: {
                                        type: 'time', // Required for chronological data
                                        time: {
                                            unit: 'hour' 
                                        },
                                        title: {
                                            display: true,
                                            text: 'Time'
                                        }
                                    },
                                    y: {
                                        min: 0,
                                        max: 100,
                                        title: {
                                            display: true,
                                            text: 'Water Level (%)'
                                        }
                                    }
                                }
                            }
                        });
                        
                        // Listen for the unique event dispatched from the Livewire component 
                        // when data is updated (e.g., timeRange is changed).
                        Livewire.on(this.livewireEventName, (event) => {
                            if (this.chart && event.chartData) {
                                // Update chart data and re-draw the chart
                                this.chart.data.datasets = event.chartData;
                                this.chart.update(); 
                            }
                        });
                    }
                }"
                x-ref="canvas"
            ></canvas>
        </div>
        
        {{-- Data statistics section --}}
        <div class="mt-4 text-xs text-gray-400 text-center">
            {{-- Count data points safely using Laravel's collect() on the PHP array --}}
            <p>Data points: {{ collect($chartData)->flatten(1)->filter(fn($item) => is_array($item) && isset($item['x']))->count() }}</p>
            
            {{-- Use standard PHP count() on the array --}}
            <p>Devices: {{ count($chartData) }}</p>
        </div>
    </div>
</div>

{{-- 
    Push Chart.js and Moment.js dependencies to the main layout file.
    Ensure the main layout has @stack('scripts') before </body>.
--}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.30.1/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-moment@1.0.1/dist/chartjs-adapter-moment.min.js"></script>
@endpush