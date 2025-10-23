@props(['title' => '', 'description' => ''])

<div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
    @if($title || $description)
        <div class="px-4 py-5 sm:p-6">
            @if($title)
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                    {{ $title }}
                </h3>
            @endif
            
            @if($description)
                <div class="mt-2 max-w-xl text-sm text-gray-500 dark:text-gray-400">
                    <p>{{ $description }}</p>
                </div>
            @endif
        </div>
    @endif
    
    <div class="px-4 py-5 sm:p-6">
        {{ $slot }}
    </div>
</div>
