@props(['type' => 'text', 'placeholder' => '', 'required' => false])

@php
$baseClasses = 'block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:focus:border-indigo-400 dark:focus:ring-indigo-400 sm:text-sm';

if ($attributes->has('error')) {
    $baseClasses .= ' border-red-300 dark:border-red-600 focus:border-red-500 focus:ring-red-500 dark:focus:border-red-400 dark:focus:ring-red-400';
}
@endphp

<input 
    type="{{ $type }}" 
    placeholder="{{ $placeholder }}"
    {{ $required ? 'required' : '' }}
    {{ $attributes->merge(['class' => $baseClasses]) }}
>

@if($attributes->has('error'))
    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $attributes->get('error') }}</p>
@endif
