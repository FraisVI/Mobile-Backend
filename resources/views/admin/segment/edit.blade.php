@extends('admin.layout')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">{{ $title }}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
        </div>
    </div>

    <form id="segment-form" class="form-floating needs-validation" action="{{ route('segments.update', [$segment->id]) }}" method="post" novalidate>
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="form-floating">
                    <input type="text" class="form-control" id="inputName" name="name" value="{{ $segment->name }}" required>
                    <label for="inputName">Наименование</label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-3">
                <label class="form-label">Firebase-топик</label>
                <div class="form-control-plaintext bg-light px-2 py-2 rounded">{{ $segment->firebase_topic ?? '—' }}</div>
                <small class="form-text text-muted"><strong>Имя топика изменить нельзя.</strong></small>
                @if(!empty($segment->firebase_topic))
                    @php
                        $status = $segment->topic_subscription_status ?? null;
                        $statusConfig = [
                            'pending' => ['text' => 'В очереди', 'class' => 'bg-secondary'],
                            'in_progress' => ['text' => 'Идёт загрузка данных', 'class' => 'bg-warning text-dark'],
                            'completed' => ['text' => 'Завершено', 'class' => 'bg-success'],
                            'failed' => ['text' => 'Ошибка', 'class' => 'bg-danger'],
                        ];
                        $cfg = $statusConfig[$status] ?? null;
                    @endphp
                    @if($cfg)
                        <div class="mt-2"><span class="badge {{ $cfg['class'] }}">{{ $cfg['text'] }}</span></div>
                    @endif
                @endif
            </div>
        </div>

        @include('admin.segment.user-add-field')

        <div class="row">
            <div class="col-lg-2">
                <button class="w-100 btn btn-lg btn-dark">Сохранить</button>
            </div>
        </div>

        <br /><br />
    </form>

    <script>
        $(document).ready(function() {
            moment.locale('ru');

            $("#segment-form").submit(function (event) {
                this.classList.add('was-validated');
                if (!this.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()

                    $('html,body').animate(
                        {scrollTop: $('.form-select:invalid, .form-control:invalid').first().offset().top - 100}
                        ,'slow'
                    );
                    return false;
                }

                return true;
            });
        });
    </script>
@endsection
