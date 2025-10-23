@props(['title' => '', 'description' => ''])

<div class="bg-white dark:bg-gray-800 shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        @if($title)
            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">
                {{ $title }}
            </h3>
        @endif
        
        @if($description)
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                {{ $description }}
            </p>
        @endif
        
        {{ $slot }}
    </div>
</div>
