@vite(['resources/css/app.css'])
<div class="flex items-center gap-3">
    <img
        src="{{ asset('images/logo/cnsm.png') }}"
        alt="Logo CNSM"
        class="h-9 shrink-0"
    />

    <span class="text-sm font-bold tracking-tight text-gray-950 dark:text-white leading-tight">
        {{ filament()->getBrandName() }}
    </span>
</div>
