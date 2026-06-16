@extends('admin.layout')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">{{ $title }}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
        </div>
    </div>

    <style>
        .sessions-table td:nth-child(5) {
            font-size: 10px;
            max-width: 200px;
            white-space: nowrap;
            text-overflow: ellipsis;
            overflow: hidden;
        }
    </style>

    <div class="table-responsive">
        <table class="table table-sm table-striped sessions-table">
            <thead>
            <tr>
                <th>ID</th>
                @include('admin.partials.sort-th', ['column' => 'lastused', 'label' => 'Последняя активность', 'sort' => $sort, 'order' => $order])
                <th>ФИО</th>
                @include('admin.partials.sort-th', ['column' => 'version', 'label' => 'AppVersion', 'sort' => $sort, 'order' => $order])
                <th>UAgent</th>
                <th>Push token</th>
                @include('admin.partials.sort-th', ['column' => 'authorized', 'label' => 'Авторизован', 'sort' => $sort, 'order' => $order])
                @include('admin.partials.sort-th', ['column' => 'registered', 'label' => 'Зарегистрирован', 'sort' => $sort, 'order' => $order])
            </tr>
            </thead>
            <tbody>
            @foreach($sessions as $s)
                @php $u = $users->get($s->user_id); @endphp
                <tr>
                    <td>{{ $u ? $u->id : $s->user_id }}</td>
                    <td>{{ $s->lastused ? $s->lastused->diffForHumans() : '—' }}</td>
                    <td>
                        @if($u)
                            {{ $u->lastname }} {{ $u->firstname }} {{ $u->middlename }} (+7{{ $u->phone }})
                        @else
                            — (user_id: {{ $s->user_id }})
                        @endif
                    </td>
                    <td>{{ $s->version ?? '—' }}</td>
                    <td title="{{ $s->uagent }}">{{ $s->uagent }}</td>
                    <td>{{ ($s->fcm_token != null) ? '+' : '' }}</td>
                    <td>{{ ($s->authorized == 1) ? '+' : '' }}</td>
                    <td>{{ ($s->registered == 1) ? '+' : '' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    @include('admin.partials.pagination-toolbar', ['paginator' => $sessions, 'perPage' => $perPage])
@endsection
