@extends('admin.layout')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">{{ $title }}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
        </div>
    </div>

    <form id="segment-form" class="form-floating needs-validation" action="{{ route('segments.store') }}" method="post" novalidate>
        @csrf

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="form-floating">
                    <input type="text" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" id="inputName" name="name" value="{{ old('name') }}" required>
                    <label for="inputName">Наименование</label>
                </div>
                @error('name')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="form-floating">
                    <input type="text" class="form-control {{ $errors->has('firebase_topic') ? 'is-invalid' : '' }}" id="inputFirebaseTopic" name="firebase_topic" value="{{ old('firebase_topic') }}" placeholder="promo, news..." maxlength="255" pattern="[a-zA-Z0-9_-]*" title="Только латиница, цифры, _ и -">
                    <label for="inputFirebaseTopic">Firebase-топик (необязательно)</label>
                </div>
                @error('firebase_topic')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Только a–z, A–Z, 0–9, _ и -. Пробелы запрещены. Если пусто — будет topic_ID. <strong>После создания имя топика изменить нельзя.</strong></small>
            </div>
        </div>

        @include('admin.segment.user-add-field')

        <div class="row">
            <div class="col-lg-2">
                <button class="w-100 btn btn-lg btn-dark">Создать</button>
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
