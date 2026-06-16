@php
    $query = request()->query();
    $query['page'] = 1;
@endphp
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3">
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <span class="text-muted small">
            Показано {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} из {{ $paginator->total() }}
        </span>
        <span class="text-muted small">Записей на странице:</span>
        <div class="btn-group btn-group-sm" role="group">
            @foreach([25, 50, 100] as $n)
                @php $q = array_merge($query, ['per_page' => $n]); @endphp
                <a href="{{ request()->url() . '?' . http_build_query($q) }}" class="btn btn-sm {{ ($perPage ?? 50) == $n ? 'btn-primary' : 'btn-outline-secondary' }}">
                    {{ $n }}
                </a>
            @endforeach
        </div>
    </div>
    <div>
        {{ $paginator->withQueryString()->links('pagination::bootstrap-4') }}
    </div>
</div>
