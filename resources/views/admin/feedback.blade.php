@extends('admin.layout')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">{{ $title }}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
        </div>
    </div>

    <style>
    </style>

    <table id="feedback-table" class="table table-striped">
        <thead>
            <tr>
                <th>User ID</th>
                <th>ФИО</th>
                <th style="width: 150px;">Активность</th>
                <th>Последнее сообщение</th>
                <th style="width: 150px;">Управление</th>
            </tr>
        </thead>
        <tbody class="table-group-divider">
        <?php /** @var \App\Models\Feedback[] $feedbacks */ ?>
        <?php /** @var \App\Models\AppUser[] $users */ ?>
        @foreach($feedbacks as $f)
            <tr>
                <td>{{ $users[$f->user_id]->id }}</td>
                <td>{{ $users[$f->user_id]->fio() }}</td>
                <td>
                    {{ $f->created_at->format('d.m.Y H:m') }} <br />
                    <small> {{ $f->created_at->diffForHumans() }} </small>
                </td>
                <td>
                    {{ Str::limit($f->message, 200) }}<br /><br />
                    @if($f->from_user == 1 && $f->viewed == 0)
                        <span class="badge bg-danger">Пользователь ждёт ответа</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('feedback.show', [$f->user_id]) }}" class="btn btn-primary btn-sm">Перейти к чату</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
