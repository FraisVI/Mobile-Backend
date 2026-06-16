@extends('admin.layout')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">{{ $title }}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
        </div>
    </div>

    <?php /** @var \App\Models\Shop $shop */ ?>
    <form id="shops-form" class="form-floating needs-validation" action="{{ route('shops.store') }}" method="post" novalidate>
        @csrf

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="form-floating">
                    <input type="text" class="form-control" id="inputTitle" name="name" required>
                    <label for="inputTitle">Наименование</label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="form-floating">
                    <select class="form-select" id="selectCity" name="city_id">
                        @foreach($cities as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                    <label for="selectCity">Город</label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="form-floating">
                    <input type="text" class="form-control" id="inputAddress" name="address" required>
                    <label for="inputAddress">Адрес</label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="form-floating">
                    <input type="text" class="form-control" id="inputText" name="text">
                    <label for="inputText">Строчка выделенная красным (опционально)</label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3 mb-3">
                <div class="form-floating">
                    <input type="text" class="form-control" id="inputPhone" name="phone" required>
                    <label for="inputPhone">Телефон</label>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="form-floating">
                    <input type="text" class="form-control" id="inputWorkingHours" name="working_hours" required>
                    <label for="inputWorkingHours">Время работы</label>
                </div>
            </div>
        </div>

        <div class="row">
            <h3 class="mt-4">Координаты</h3>
            <div class="col-lg-3 mb-3">
                <div class="form-floating">
                    <input type="text" class="form-control" id="inputLat" name="lat" required>
                    <label for="inputLat">Широта</label>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="form-floating">
                    <input type="text" class="form-control" id="inputLon" name="lon" required>
                    <label for="inputLon">Долгота</label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-2">
                <button class="w-100 btn btn-lg btn-dark">Добавить</button>
            </div>
        </div>

        <br /><br />
    </form>

    <script>
        $(document).ready(function() {
            moment.locale('ru');

            $("#shops-form").submit(function (event) {
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
