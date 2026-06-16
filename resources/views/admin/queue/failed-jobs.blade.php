@extends('admin.layout')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">{{ $title }}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            @if($failedJobs->total() > 0)
                <form action="{{ route('queue.retry-all') }}" method="post" style="display: inline; margin-right: 10px;">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Повторить все упавшие задачи?')">
                        <i class="fa fa-refresh" aria-hidden="true"></i> Повторить все
                    </button>
                </form>
                <form action="{{ route('queue.flush-all') }}" method="post" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Удалить все упавшие задачи? Это действие необратимо!')">
                        <i class="fa fa-trash" aria-hidden="true"></i> Очистить все
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if($failedJobs->total() === 0)
        <div class="alert alert-success" role="alert">
            <i class="fa fa-check-circle" aria-hidden="true"></i> Нет упавших задач. Всё работает отлично!
        </div>
    @else
        <div class="alert alert-warning" role="alert">
            <strong>Всего упавших задач:</strong> {{ $failedJobs->total() }}
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-sm">
                <thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th style="width: 150px;">UUID</th>
                    <th style="width: 120px;">Очередь</th>
                    <th>Задача</th>
                    <th style="width: 180px;">Упала</th>
                    <th style="width: 200px;">Управление</th>
                </tr>
                </thead>
                <tbody>
                @foreach($failedJobs as $job)
                    @php
                        $payload = json_decode($job->payload, true);
                        $displayName = $payload['displayName'] ?? 'Unknown Job';
                        $exceptionPreview = \Illuminate\Support\Str::limit($job->exception, 200);
                    @endphp
                    <tr>
                        <td>{{ $job->id }}</td>
                        <td><small>{{ $job->uuid }}</small></td>
                        <td><span class="badge bg-secondary">{{ $job->queue }}</span></td>
                        <td>
                            <strong>{{ $displayName }}</strong>
                            <br>
                            <small class="text-muted">{{ $exceptionPreview }}</small>
                            <br>
                            <button class="btn btn-link btn-sm p-0" type="button" data-bs-toggle="collapse" data-bs-target="#exception-{{ $job->id }}" aria-expanded="false">
                                Показать полную ошибку
                            </button>
                            <div class="collapse mt-2" id="exception-{{ $job->id }}">
                                <pre class="bg-light p-2" style="font-size: 11px; max-height: 300px; overflow-y: auto;">{{ $job->exception }}</pre>
                            </div>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($job->failed_at)->format('d.m.Y H:i:s') }}<br><small class="text-muted">{{ \Carbon\Carbon::parse($job->failed_at)->diffForHumans() }}</small></td>
                        <td>
                            <div class="btn-group" role="group">
                                <form action="{{ route('queue.retry-job', $job->id) }}" method="post" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm" title="Повторить задачу">
                                        <i class="fa fa-refresh" aria-hidden="true"></i>
                                    </button>
                                </form>
                                <form action="{{ route('queue.forget-job', $job->id) }}" method="post" style="display: inline;" onsubmit="return confirm('Удалить эту задачу?')">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm" title="Удалить задачу">
                                        <i class="fa fa-trash" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $failedJobs->links('pagination::bootstrap-4') }}
        </div>
    @endif
@endsection
