@props([
    'title' => null,
    'description' => 'Cari kamar kost yang tersedia di Bekasi dan Cikarang, ajukan sewa online, dan bayar tagihan dengan bukti transfer yang tercatat rapi.',
    'image' => null,
    'bodyClass' => 'bg-kapur-50',
])

@php
    $fullTitle = $title ? $title.' · Kost Antik' : 'Kost Antik · Kamar kost yang jelas, tagihan yang rapi';
@endphp

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0B3B37">

    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ $description }}">

    <meta property="og:site_name" content="Kost Antik">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $fullTitle }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if ($image)
        <meta property="og:image" content="{{ $image }}">
    @endif
    <meta property="og:locale" content="id_ID">

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-[100dvh] font-sans text-ink-900 {{ $bodyClass }}">
    <a href="#konten" class="sr-only z-toast rounded-lg bg-tegel-700 px-4 py-2 text-sm font-semibold text-white focus:not-sr-only focus:fixed focus:left-4 focus:top-4">
        Lewati ke konten
    </a>

    {{ $slot }}

    <x-toast />
    @stack('scripts')
</body>
</html>
