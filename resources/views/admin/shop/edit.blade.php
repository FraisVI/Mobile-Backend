@extends('admin.layout')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">{{ $title }}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
        </div>
    </div>

    <style>
        .images {
            list-style: none;
            display: flex;
            flex-wrap: wrap;
        }

        .images li {
            margin-right: 5px;
            margin-bottom: 5px;
            position: relative;
        }

        .images li img {
            border-radius: 5px;
            max-width: 150px;
            max-height: 100px;
        }

        .images .fa-times-circle {
            position: absolute;
            top: 5px;
            right: 5px;
            font-size: 16px;
            padding: 5px;
            border-radius: 15px;
            cursor: pointer;
            text-shadow: 0 0 5px #ffffff, 0 0 5px #ffffff;
        }

        .deleted {
            opacity: 0.5;
        }
    </style>

    <?php /** @var \App\Models\Shop $shop */ ?>
    <form id="shops-form" class="form-floating needs-validation" action="{{ route('shops.update', [$shop->id]) }}" method="post" enctype="multipart/form-data" novalidate>
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="form-floating">
                    <input type="text" class="form-control" id="inputTitle" name="name" value="{{ $shop->name }}" required>
                    <label for="inputTitle">Наименование</label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="form-floating">
                    <select class="form-select" id="selectCity" name="city_id">
                        @foreach($cities as $c)
                            <option value="{{ $c->id }}"{{ ( $c->id == $shop->city_id) ? ' selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    <label for="selectCity">Город</label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="form-floating">
                    <input type="text" class="form-control" id="inputAddress" name="address" value="{{ $shop->address }}" required>
                    <label for="inputAddress">Адрес</label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="form-floating">
                    <input type="text" class="form-control" id="inputText" name="text" value="{{ $shop->text }}">
                    <label for="inputText">Строчка выделенная красным (опционально)</label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3 mb-3">
                <div class="form-floating">
                    <input type="text" class="form-control" id="inputPhone" name="phone" value="{{ $shop->phone }}" required>
                    <label for="inputPhone">Телефон</label>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="form-floating">
                    <input type="text" class="form-control" id="inputWorkingHours" name="working_hours" value="{{ $shop->working_hours }}" required>
                    <label for="inputWorkingHours">Время работы</label>
                </div>
            </div>
        </div>

        <div class="row">
            <h3 class="mt-4">Координаты</h3>
            <div class="col-lg-3 mb-3">
                <div class="form-floating">
                    <input type="text" class="form-control" id="inputLat" name="lat" value="{{ $shop->lat }}" required>
                    <label for="inputLat">Широта</label>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="form-floating">
                    <input type="text" class="form-control" id="inputLon" name="lon" value="{{ $shop->lon }}" required>
                    <label for="inputLon">Долгота</label>
                </div>
            </div>
        </div>

        @if ($shop->images)
        <div class="row">
            <h3 class="mt-4">Изображения</h3>
            <ul class="images">
                @foreach($shop->images as $i)
                <li>
                    <img src="{{ URLHelper::transform($i) }}"/>
                    <i class="fa fa-times-circle" aria-hidden="true" title="Удалить"></i>
                    <input type="hidden" name="active_images[]" value="{{ $i }}">
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="row mb-4">
            <h3 class="mt-4">Загрузить изображения</h3>
            <div class="col-lg-6 mb-3">
                <input type="file" class="form-control" name="images[]" multiple accept="image/jpeg, image/png, image/gif">
            </div>
        </div>

        <div class="row">
            <div class="col-lg-2">
                <button class="w-100 btn btn-lg btn-dark">Сохранить</button>
            </div>
        </div>

        <br /><br />
    </form>

    <script>
        $(document).ready(function() {
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

            $(".fa-times-circle").click(function() {
                $(this).parent().toggleClass('deleted');
                let input = $(this).parent().find("input[type='hidden']");
                input.prop('disabled', !input.prop('disabled'));
            });

            $('.images').sortable({
                swapThreshold: 0.30,
                animation: 150
            });
        });
    </script>
@endsection
