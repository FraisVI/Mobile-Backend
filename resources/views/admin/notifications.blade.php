@extends('admin.layout')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">{{ $title }}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
        </div>
    </div>

    <form id="notifications-form" class="form-floating needs-validation" onSubmit="if(!confirm('Сообщения будут разосланы выбранным получателям. Продолжить?')){return false;}" action="{{ route('notification.send') }}" method="post" novalidate>
    @csrf

        <div class="row">
            <div class="col-lg-6 mb-3">
                <label for="selectSegment" class="mb-2">Сегмент получателей:</label>
                <select class="form-select" id="selectSegment" size="8" name="segment_id[]" multiple required>
                    @foreach($segments as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
                <small class="form-text text-muted">Выберите ровно один сегмент.</small>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="form-floating">
                    <select class="form-select" id="selectArticle" name="article_id">
                        <option value="">Не выбрано</option>
                        @foreach($articles as $a)
                            <option data-title="{{ $a->title }}" data-text="{{ $a->subtitle }}" value="{{ $a->id }}">{{ $a->title }}</option>
                        @endforeach
                    </select>
                    <label for="selectArticle">Цель перехода</label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="form-floating">
                    <input type="text" class="form-control" id="inputTitle" name="title" maxlength="128" required>
                    <small style="float: right;" class="form-text text-muted"></small>
                    <label for="inputMessage">Заголовок сообщения</label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="form-floating">
                    <textarea style="height: 200px;" class="form-control" id="inputMessage" name="message" maxlength="512" placeholder="Текст сообщения" required></textarea>
                    <small style="float: right;" class="form-text text-muted"></small>
                    <label for="inputMessage">Текст сообщения</label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="form-floating">
                    <select class="form-select" id="selectLabel" name="label">
                        <option value="">Без метки</option>
                        @foreach($labels as $k => $v)
                            <option value="{{ $k }}">{{ $k }}</option>
                        @endforeach
                    </select>
                    <label for="selectLabel">Метка</label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="top_priority" value="true" id="top-priority" checked>
                    <label class="form-check-label" for="top-priority">
                        Высокий приоритет
                    </label>
                    <small style="line-height: 14px; display: inline-block;" class="form-text text-muted">
                        Пробуждает устройство из режима энергосбережения, уменьшает время доставки сообщения, увеличивает шанс получения картинки.
                        Использовать только в случае, если данное сообщение подразумевает дальнейший переход в приложение.
                    </small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-2 mb-3">
                <button class="w-100 btn btn-lg btn-dark">Отправить</button>
            </div>
        </div>

    </form>

    <script>
        $(document).ready(function() {
            moment.locale('ru');

            $("#selectArticle").change(function () {
                const selected = $(this).find(":selected");
                $("#inputTitle").val( selected.data('title') ).trigger('keyup');
                $("#inputMessage").val( selected.data('text') ).trigger('keyup');
            });

            $("#notifications-form").submit(function (event) {
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

            function countCharacters() {
                const max = $(this).attr("maxlength");
                const length = $(this).val().length;
                const helper = $(this).next(".form-text");

                helper.text(length + " / " + max);
            }

            const countableInputs = $(".form-control");
            countableInputs.each(countCharacters);
            countableInputs.keyup(countCharacters);
        });
    </script>

@endsection
