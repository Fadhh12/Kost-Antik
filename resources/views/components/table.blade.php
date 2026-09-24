{{--
    Tabel responsif. Di < md, setiap baris berubah jadi kartu:
    beri setiap <td> atribut data-label="Judul kolom".
    Kolom uang: class="is-money" (rata kanan, tabular).
--}}
@props(['head' => null])

<div {{ $attributes->merge(['class' => 'rtable-wrap']) }}>
    <table class="rtable">
        @if ($head)
            <thead>
                <tr>{{ $head }}</tr>
            </thead>
        @endif
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
