@props(['large' => false])
<div class="flex items-center space-x-2">
    <img src="{{ Vite::asset('resources/images/logo.png') }}" alt="Spent" @class($large ? 'max-h-11' : 'max-h-8')">
    <span class="{{ $large ? 'text-xl' : 'text-base'}} font-bold text-accent dark:text-accent-content">{{ config('app.name', 'Spent') }}</span>
    <span class="sr-only">{{ config('app.name', 'Spent') }}</span>
</div>
