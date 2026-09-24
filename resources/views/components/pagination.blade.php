{{-- Pagination yang mempertahankan query string filter (SRS 2.4). --}}
@props(['paginator'])

@if ($paginator->hasPages())
    <div {{ $attributes->merge(['class' => 'mt-6']) }}>
        {{ $paginator->withQueryString()->links() }}
    </div>
@endif
