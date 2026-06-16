@extends('admin.layout')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">{{ $title }}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-sm table-striped">
            <thead>
            <tr>
                @include('admin.partials.sort-th', ['column' => 'time', 'label' => 'Отправлено', 'sort' => $sort, 'order' => $order])
                @include('admin.partials.sort-th', ['column' => 'title', 'label' => 'Содержимое', 'sort' => $sort, 'order' => $order])
                @include('admin.partials.sort-th', ['column' => 'total', 'label' => 'Получатели', 'sort' => $sort, 'order' => $order])
                @include('admin.partials.sort-th', ['column' => 'sent', 'label' => 'Отправлено', 'sort' => $sort, 'order' => $order])
                <th>Прочитали</th>
            </tr>
            </thead>
            <tbody>
            @foreach($logs as $l)
                @php
                    $time = is_object($l->time) ? $l->time : \Carbon\Carbon::parse($l->time);
                @endphp
                <tr>
                    <td>{{ $time->format('d.m.Y H:i:s') }}<br /><small class="text-muted">{{ $time->diffForHumans() }}</small></td>
                    <td>{{ $l->title }}<br /><small class="text-muted">{{ $l->message }}</small></td>
                    <td>{{ $l->total ?? 0 }}</td>
                    <td>{{ $l->sent ?? 0 }}</td>
                    <td>{{ $l->read ?? '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    @include('admin.partials.pagination-toolbar', ['paginator' => $logs, 'perPage' => $perPage])
@endsection
