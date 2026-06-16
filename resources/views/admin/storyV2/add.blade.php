@extends('admin.layout')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">{{ $title }}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
        </div>
    </div>

    <?php /** @var \App\Models\StoriesV2 $story */ ?>
    <form id="stories-form" class="form-floating needs-validation" action="{{ route('storiesV2.store') }}" method="post" enctype="multipart/form-data" novalidate>
        @csrf

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="form-floating">
                    <input type="text" class="form-control" id="inputTitle" name="title" required>
                    <label for="inputTitle">Заголовок</label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="form-floating">
                    <input type="text" class="form-control" id="inputDuration" name="duration" value="8" required>
                    <label for="inputDuration">Время показа (сек)</label>
                </div>
            </div>
        </div>

        <div class="row">
            <h3 class="mt-4">Загрузить превью <small style="font-size: 12px;">150x200</small></h3>
            <div class="row">
                <div class="col-lg-6 mb-3">
                    <input type="file" class="form-control" name="preview" accept="image/jpeg, image/png, image/gif">
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <h3 class="mt-4">Загрузить слайды <small style="font-size: 12px;">428x928</small></h3>
            <div class="row">
                <div class="col-lg-6 mb-3">
                    <input type="file" class="form-control" name="images[]" accept="image/jpeg, image/png, image/gif" multiple>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="inputPublished" name="published" value="1">
                    <label class="form-check-label" for="inputPublished">
                        Опубликована (Станет видимой для пользователей в приложении)
                    </label>
                </div>
            </div>
        </div>

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
