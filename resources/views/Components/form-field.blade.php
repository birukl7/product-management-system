@props(['name' => null])

<div {{ $attributes->merge(['class' => 'mb-4']) }}>
    {{ $slot }}
</div>