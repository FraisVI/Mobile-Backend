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
                @include('admin.partials.sort-th', ['column' => 'id', 'label' => 'ID', 'sort' => $sort, 'order' => $order])
                @include('admin.partials.sort-th', ['column' => 'lastname', 'label' => 'ФИО', 'sort' => $sort, 'order' => $order])
                @include('admin.partials.sort-th', ['column' => 'email', 'label' => 'Email', 'sort' => $sort, 'order' => $order])
                @include('admin.partials.sort-th', ['column' => 'phone', 'label' => 'Телефон', 'sort' => $sort, 'order' => $order])
                @include('admin.partials.sort-th', ['column' => 'birthdate', 'label' => 'Дата рождения', 'sort' => $sort, 'order' => $order])
                @include('admin.partials.sort-th', ['column' => 'gender', 'label' => 'Пол', 'sort' => $sort, 'order' => $order])
                @include('admin.partials.sort-th', ['column' => 'bonus_count', 'label' => 'Бонусы', 'sort' => $sort, 'order' => $order])
                @include('admin.partials.sort-th', ['column' => 'bonus_rate', 'label' => 'Уровень скидки', 'sort' => $sort, 'order' => $order])
                @include('admin.partials.sort-th', ['column' => 'sum_next_level', 'label' => 'До след. уровня', 'sort' => $sort, 'order' => $order])
                @include('admin.partials.sort-th', ['column' => 'updated_at', 'label' => 'Синхронизирован', 'sort' => $sort, 'order' => $order])
            </tr>
            </thead>
            <tbody>
            @foreach($users as $u)
                <tr>
                    <td>{{ $u->id }}</td>
                    <td>{{ $u->lastname }} {{ $u->firstname }} {{ $u->middlename }}</td>
                    <td>{{ $u->email }}</td>
                    <td>+7{{ $u->phone }}</td>
                    <td>{{ $u->birthdate ? $u->birthdate->format('d.m.Y') : '—' }}</td>
                    <td>{{ $u->gender == 'f' ? 'Ж' : ($u->gender == 'm' ? 'М' : '') }}</td>
                    <td>{{ $u->bonus_count }}</td>
                    <td>{{ $u->bonus_rate }}</td>
                    <td>{{ $u->sum_next_level }}</td>
                    <td>{{ $u->updated_at ? $u->updated_at->diffForHumans() : '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    @include('admin.partials.pagination-toolbar', ['paginator' => $users, 'perPage' => $perPage])
@endsection
