@extends('admin.layout')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">{{ $title }}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
        </div>
    </div>

    <?php /** @var \App\Models\Article $article */ ?>
    <form id="article-form" class="form-floating needs-validation" action="{{ route('articles.store') }}" method="post" novalidate>
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
                    <input type="text" class="form-control" id="inputSubtitle" name="subtitle" required>
                    <label for="inputSubtitle">Краткое описание</label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="form-floating">
                    <input type="text" class="form-control" id="inputHref" name="href">
                    <label for="inputHref">Ссылка для кнопки "Подробнее на сайте"</label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3 mb-3">
                <div class="form-floating">
                    <select class="form-select" id="selectType" name="type_id" required>
                        <option value="1">Новость</option>
                        <option value="2">Онлайн</option>
                        <option value="3">Оффлайн</option>
                        <option value="4">Онлайн + Оффлайн</option>
                    </select>
                    <label for="selectType">Тип акции</label>
                </div>
            </div>
        </div>

        <div class="row">
            <h3 class="mt-4">Время действия</h3>
            <small style="padding-bottom: 7px;">
                Формат даты: ГГГГ-ММ-ДД ЧЧ:ММ:СС; <br />
                Пустые поля - бесрочная акция до отмены;<br />
                Заполнено только начало - активна от начала до отмены;<br />
                Для новостей данные поля игнорируются<br />
            </small>
            <div class="col-lg-3 mb-3">
                <div class="form-floating">
                    <input type="text" class="form-control" id="inputStartAt" name="start_at">
                    <label for="inputStartAt">Начало</label>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="form-floating">
                    <input type="text" class="form-control" id="inputEndAt" name="end_at">
                    <label for="inputEndAt">Конец</label>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <h3 class="mt-4">Текст (HTML)</h3>
            <div class="row">
                <div class="col-lg-6 mb-3">
                <textarea class="form-control" rows="12" name="content"
                          placeholder=""></textarea>
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

            $("#article-form").submit(function (event) {
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

            let pickers = $('input[name="start_at"], input[name="end_at"]');

            pickers.daterangepicker({
                singleDatePicker: true,
                autoUpdateInput: false,
                timePicker: true,
                timePicker24Hour: true,
                locale: {
                    format: 'YYYY-MM-DD HH:mm:ss',
                    applyLabel: 'Применить',
                    cancelLabel: 'Отмена',
                },
            });

            pickers.on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm:ss'));
            });

            pickers.on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });

            $('textarea[name="content"]').summernote({ height: 300 });
        });
    </script>
@endsection
