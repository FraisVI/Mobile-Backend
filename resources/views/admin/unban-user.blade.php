@extends('admin.layout')

@section('content')
    <div class="container">
        <h2>Разблокировать пользователя по номеру</h2>
        <span>Номер необходимо вводить +7XXXXXXXXXX без пробелов и тире</span>
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if(session('info'))
            <div class="alert alert-info">{{ session('info') }}</div>
        @endif

        <form action="{{ route('unban-user.process') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="phone" class="form-label"></label>
                <input type="text" name="phone" id="phone" class="form-control" placeholder="+7XXXXXXXXXX" required>
            </div>

            <button type="submit" class="btn btn-primary">Разбанить</button>
        </form>
    </div>
@endsection
