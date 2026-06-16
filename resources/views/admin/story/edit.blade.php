@extends('admin.layout')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">{{ $title }}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
        </div>
    </div>

    <?php /** @var \App\Models\Stories $story */ ?>
    <form id="stories-form" class="form-floating needs-validation" action="{{ route('stories.update', [$story->id]) }}" method="post" enctype="multipart/form-data" novalidate>
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="form-floating">
                    <input type="text" class="form-control" id="inputTitle" name="title" value="{{ $story->title }}" required>
                    <label for="inputTitle">Заголовок</label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="form-floating">
                    <input type="text" class="form-control" id="inputHref" name="href" value="{{ $story->href }}">
                    <label for="inputHref">Ссылка на "Подробнее..."</label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="form-floating">
                    <input type="text" class="form-control" id="inputDuration" name="duration" value="{{ $story->duration / 1000 }}" required>
                    <label for="inputDuration">Время показа (сек)</label>
                </div>
            </div>
        </div>

        @if ($story->url)
            <div class="row mb-4">
                <h3 class="mt-4">Изображение</h3>
                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <img style="max-height: 256px; max-width: 256px;" src="{{ URLHelper::transform($story->url) }}" />
                    </div>
                </div>
            </div>
        @endif

        <div class="row mb-4">
            <h3 class="mt-4">Сменить изображение</h3>
            <div class="row">
                <div class="col-lg-6 mb-3">
                    <input type="file" class="form-control" name="image" accept="image/jpeg, image/png, image/gif">
                </div>
            </div>
        </div>

        @if ($story->thumb)
            <div class="row mb-4">
                <h3 class="mt-4">Превью</h3>
                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <img style="max-height: 200px; max-width: 150px;" src="{{ URLHelper::transform($story->thumb) }}" />
                    </div>
                </div>
            </div>
        @endif

        <div class="row mb-4">
            <h3 class="mt-4">Сменить превью</h3>
            <div class="row">
                <div class="col-lg-6 mb-3">
                    <input type="file" class="form-control" name="thumb" accept="image/jpeg, image/png, image/gif">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="inputPublished" name="published" value="1" {{ $story->published == 1 ? 'checked' : '' }}>
                    <label class="form-check-label" for="inputPublished">
                        Опубликована (Станет видимой для пользователей в приложении)
                    </label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-2">
                <button class="w-100 btn btn-lg btn-dark">Изменить</button>
            </div>
        </div>

        <br /><br />
    </form>

    <script>
        $(document).ready(function() {
            moment.locale('ru');

            $("#stories-form").submit(function (event) {
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
