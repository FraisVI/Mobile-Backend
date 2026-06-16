@extends('admin.layout')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">{{ $title }}</h1>
    </div>

    <p class="text-muted small mb-3">
        Аналитика по рассылкам собирается только в течение <strong>3 дней</strong> после отправки. Приложение отправляет события (доставлено, открыто, переход) на endpoint <code>POST /api/push-event</code> с телом <code>{"send_id": id, "event": "delivered"|"opened"|"click"}</code>.
    </p>

    <div class="table-responsive">
        <table class="table table-sm table-striped">
            <thead>
            <tr>
                <th>Дата отправки</th>
                <th>Топик</th>
                <th>Заголовок</th>
                <th>Отправлено</th>
                <th>Доставлено</th>
                <th>% доставл.</th>
                <th>Открытий</th>
                <th>% от доставл.</th>
                <th>Переходов</th>
                <th>% от открытий</th>
                <th>Сбор до</th>
            </tr>
            </thead>
            <tbody>
            @foreach($reports as $r)
                <tr>
                    <td>{{ $r->time->format('d.m.Y H:i') }}</td>
                    <td>{{ $r->topic_name }}</td>
                    <td title="{{ $r->message }}">{{ \Illuminate\Support\Str::limit($r->title, 30) }}</td>
                    <td>{{ $r->sent }}</td>
                    <td>{{ $r->delivered }}</td>
                    <td>{{ $r->pct_delivered }}%</td>
                    <td>{{ $r->opened }}</td>
                    <td>{{ $r->pct_opened }}%</td>
                    <td>{{ $r->clicks }}</td>
                    <td>{{ $r->pct_clicks }}%</td>
                    <td>
                        {{ $r->analytics_until->format('d.m.Y H:i') }}
                        @if($r->collecting)
                            <span class="badge bg-success">идёт сбор</span>
                        @else
                            <span class="badge bg-secondary">завершён</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    @if(empty($reports))
        <div class="alert alert-info">Нет рассылок по топикам для отчёта.</div>
    @endif
@endsection
