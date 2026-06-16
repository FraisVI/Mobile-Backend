@extends('admin.layout')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">{{ $title }}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
        </div>
    </div>

    <style>
        .fa-android, .fa-apple {
            font-size: 26px;
        }
        .fa-android {
            color: #78C257;
        }

        tr td:nth-child(3) {
            vertical-align: middle;
            text-align: center;
        }

        tr th:nth-child(3) {
            text-align: center;
        }
    </style>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 40px;">ID</th>
                <th style="width: 280px;">CardID</th>
                <th>OS</th>
                <th>Пользователь</th>
                <th>Синхронизирован</th>
            </tr>
        </thead>
        <tbody>
        <?php /** @var \App\Models\AppUser[] $users */ ?>
        @foreach($users as $u)
            <tr @class(['table-danger' => $u->failed == 1, 'table-warning' => $u->failed == 2])>
                <td>{{ $u->id }}</td>
                <td>{{ $u->client_card_id }}</td>
                <td>
                    @if ($u->android)<i class="fa fa-android" aria-hidden="true"></i>@endif
                    @if ($u->ios)<i class="fa fa-apple" aria-hidden="true"></i>@endif
                </td>
                <td>
                    {{ $u->shortFio() }}<br />
                    <small>+7{{ $u->phone }}</small>
                </td>
                <td>{{ $u->updated_at->diffForHumans() }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
