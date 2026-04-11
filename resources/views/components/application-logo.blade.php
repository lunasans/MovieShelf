@props(['small' => false])

@if($small)
    <img src="{{ asset('img/logo/logo_small.png') }}" alt="Logo" {{ $attributes->merge(['class' => 'h-8 w-auto']) }}>
@else
    <img src="{{ asset('img/logo/logo.png') }}" alt="Logo" {{ $attributes->merge(['class' => 'h-12 w-auto']) }}>
@endif
