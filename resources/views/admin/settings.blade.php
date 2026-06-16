@extends('admin.layout')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">{{ $title }}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="post" class="form-floating needs-validation" novalidate>
        @csrf

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="form-floating">
                    <input type="number" class="form-control @error('notifications_prune_limit') is-invalid @enderror"
                           id="notifications_prune_limit" name="notifications_prune_limit"
                           value="{{ old('notifications_prune_limit', $notificationsPruneLimit) }}"
                           min="0" max="1000" required>
                    <label for="notifications_prune_limit">Максимум сообщений (пушей) на пользователя</label>
                </div>
                <small class="form-text text-muted">0 — не удалять старые сообщения. Больше 0 — оставлять у каждого пользователя только N последних пушей (остальные удаляет CRON).</small>
                @error('notifications_prune_limit')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-lg-2">
                <button type="submit" class="w-100 btn btn-lg btn-dark">Сохранить</button>
            </div>
        </div>
    </form>
@endsection
