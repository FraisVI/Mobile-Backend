@php
    $isActive = ($sort ?? '') === $column;
    $nextOrder = $isActive && ($order ?? 'desc') === 'desc' ? 'asc' : 'desc';
    $url = request()->fullUrlWithQuery(['sort' => $column, 'order' => $nextOrder, 'page' => 1]);
    $arrow = $isActive ? (($order ?? 'desc') === 'desc' ? ' ▼' : ' ▲') : '';
@endphp
<th>
    <a href="{{ $url }}" class="text-decoration-none text-dark">{{ $label }}{{ $arrow }}</a>
</th>
