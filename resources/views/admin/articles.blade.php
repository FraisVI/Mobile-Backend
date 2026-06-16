@extends('admin.layout')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">{{ $title }}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
        </div>
    </div>

    <style>
        #articles-table tbody tr td:nth-child(3) img {
            max-width: 64px;
            max-height: 64px;
        }
    </style>

    <a href="{{ route('articles.create') }}" class="btn btn-dark mb-3">
        <i style="padding-right: 5px;" class="fa fa-plus-circle" aria-hidden="true"></i> Создать акцию
    </a>

    <table id="articles-table" class="table">
        <thead>
        <tr>
            <th>ID</th>
            <th>Тип</th>
            <th></th>
            <th>Заголовок</th>
            <th>Начало</th>
            <th>Конец</th>
            <th>Опубликован</th>
            <th>Управление</th>
        </tr>
        </thead>
        <tbody class="table-group-divider">
        <?php /** @var \App\Models\Article[] $articles */ ?>
        @foreach($articles as $a)
            <tr>
                <td>{{ $a->id }}</td>
                <td>{{ $a->type_id == '2' ? 'Онлайн' : ($a->type_id == '3' ? 'Оффлайн' : ($a->type_id == '4' ? 'Онлайн + Оффлайн' : 'Новость')) }}</td>
                <td><img src="{{ URLHelper::transform($a->image_small ?? $a->image) }}" /></td>
                <td>
                    {{ $a->title }} <br />
                    <small>{{ $a->subtitle }}</small>
                </td>
                <td>{{ $a->start_at?->format('d.m.Y') ?? '' }}</td>
                <td>{{ $a->end_at?->format('d.m.Y') ?? '' }}</td>
                <td>{{ $a->published == 1 ? '+' : '' }}</td>
                <td>
                    <a href="{{ route('articles.edit', [$a->id]) }}" class="btn btn-primary btn-sm">Изменить</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
