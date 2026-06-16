@extends('admin.layout')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">{{ $title }}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
        </div>
    </div>

    <a href="{{ route('shops.create') }}" class="btn btn-dark mb-3">
        <i style="padding-right: 5px;" class="fa fa-plus-circle" aria-hidden="true"></i> Добавить магазин
    </a>

    <table id="shops-table" class="table">
        <thead>
        <tr>
            <th>ID</th>
            <th></th>
            <th>Заголовок</th>
            <th>Телефон</th>
            <th>Время работы</th>
            <th>Управление</th>
        </tr>
        </thead>
        <tbody class="table-group-divider">
        <?php /** @var \App\Models\City[] $cities */ ?>
        <?php /** @var \App\Models\Shop[] $shops */ ?>
        @foreach($shops as $s)
            <tr>
                <td>{{ $s->id }}</td>
                <td>{{ $cities[$s->city_id]->name }}</td>
                <td>
                    {{ $s->name }} <br />
                    <small>{{ $s->address }}</small>
                </td>
                <td>{{ $s->phone }}</td>
                <td>{{ $s->working_hours }}</td>
                <td>
                    <form onSubmit="if(!confirm('Вы уверены, что хотите удалить запись?')){return false;}" action="{{ route('shops.destroy', [$s->id]) }}" method="post">
                        @csrf
                        @method('DELETE')
                        <div class="btn-group" role="group">
                            <a href="{{ route('shops.edit', [$s->id]) }}" class="btn btn-primary btn-sm">Изменить</a>
                            <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                        </div>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
