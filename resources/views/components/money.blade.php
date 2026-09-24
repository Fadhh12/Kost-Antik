@props(['amount' => 0])

<span {{ $attributes->merge(['class' => 'num whitespace-nowrap']) }}>{{ rupiah($amount) }}</span>
