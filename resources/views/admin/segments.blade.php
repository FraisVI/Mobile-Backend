@extends('admin.layout')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">{{ $title }}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
        </div>
    </div>

    <a href="{{ route('segments.create') }}" class="btn btn-dark mb-3">
        <i style="padding-right: 5px;" class="fa fa-plus-circle" aria-hidden="true"></i> Создать сегмент
    </a>

    <table id="segments-table" class="table">
        <thead>
        <tr>
            <th style="width: 40px;">ID</th>
            <th style="width: 280px;">UUID</th>
            <th>Заголовок</th>
            <th>Топик</th>
            <th>Кол-во</th>
            <th>Создан</th>
            <th>Обновлен</th>
            <th>Опции</th>
            <th>Управление</th>
        </tr>
        </thead>
        <tbody class="table-group-divider">
        <?php /** @var \App\Models\Segment[] $segments */ ?>
        @foreach($segments as $s)
            <tr>
                <td>@if($s->id > 0){{ $s->id }}@endif</td>
                <td>{{ $s->uuid }}</td>
                <td>
                    @if($s->uuid != '')<span class="badge bg-warning text-dark">1C</span>@endif
                    {{ $s->name }}
                </td>
                <td>
                    @if($s->id === -1)
                        SendAll
                    @else
                        {{ $s->firebase_topic ?? '—' }}
                        @if(!empty($s->firebase_topic))
                            @php
                                $status = $s->topic_subscription_status ?? null;
                                $statusConfig = [
                                    'pending' => ['text' => 'В очереди', 'class' => 'bg-secondary'],
                                    'in_progress' => ['text' => 'Идёт загрузка данных', 'class' => 'bg-warning text-dark'],
                                    'completed' => ['text' => 'Завершено', 'class' => 'bg-success'],
                                    'failed' => ['text' => 'Ошибка', 'class' => 'bg-danger'],
                                ];
                                $cfg = $statusConfig[$status] ?? null;
                            @endphp
                            @if($cfg)
                                <br><span class="badge {{ $cfg['class'] }}">{{ $cfg['text'] }}</span>
                            @endif
                        @endif
                    @endif
                </td>
                <td>{{ $s->id === -1 ? '—' : ($s->users_count ?? 0) }}</td>

                <td>{{ $s->created_at->format('d.m.Y H:i:s') }}</td>
                <td>{{ $s->updated_at->diffForHumans() }}</td>

                <td>
                    <a class="btn btn-primary btn-sm" title="Просмотреть" href="{{ route('segments.list', [$s->id]) }}"><i class="fa fa-eye" aria-hidden="true"></i></a>
                    @if($s->uuid == '' && $s->id > 0)<a class="btn btn-primary btn-sm" title="Редактировать" href="{{ route('segments.edit', [$s->id]) }}"><i class="fa fa-pencil" aria-hidden="true"></i></a>@endif
                </td>

                <td>
                    @if ($s->id > 0)
                        <div class="btn-group" role="group">
                            @if($s->topic_subscription_status === 'failed')
                                <form action="{{ route('segments.resubscribe', [$s->id]) }}" method="post" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-warning btn-sm" title="Повторить подписку на топик">
                                        <i class="fa fa-refresh" aria-hidden="true"></i> Повторить
                                    </button>
                                </form>
                            @endif
                            <form onSubmit="if(!confirm('Вы уверены, что хотите удалить сегмент?')){return false;}" action="{{ route('segments.destroy', [$s->id]) }}" method="post" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                            </form>
                        </div>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
