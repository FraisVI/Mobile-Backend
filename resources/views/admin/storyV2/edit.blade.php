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
            padding-left: 0px;
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
            display: inline-block;
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

        .images .button-text {
            position: absolute;
            bottom: 15px;
            width: calc(100% - 20px);
            text-align: center;
            color: #ffffff;
            background: #D31284;
            border-radius: 2px;
            left: 10px;
            cursor: pointer;
            overflow-wrap: anywhere;
            font-size: 11px;
            padding: 2px;
            min-height: 21px;
        }

        .deleted {
            opacity: 0.5;
        }
    </style>

    <?php /** @var \App\Models\Stories $story */ ?>
    <form id="stories-form" class="form-floating needs-validation" action="{{ route('storiesV2.update', [$story->id]) }}" method="post" enctype="multipart/form-data" novalidate>
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
                    <input type="text" class="form-control" id="inputDuration" name="duration" value="{{ $story->duration / 1000 }}" required>
                    <label for="inputDuration">Время показа (сек)</label>
                </div>
            </div>
        </div>

        @if ($story->preview)
            <div class="row mb-4">
                <h3 class="mt-4">Превью</h3>
                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <img style="max-height: 200px; max-width: 150px;" src="{{ URLHelper::transform($story->preview) }}" />
                    </div>
                </div>
            </div>
        @endif

        <div class="row mb-4">
            <h3 class="mt-4">Сменить превью <small style="font-size: 12px;">150x200</small></h3>
            <div class="row">
                <div class="col-lg-6 mb-3">
                    <input type="file" class="form-control" name="preview" accept="image/jpeg, image/png, image/gif">
                </div>
            </div>
        </div>

        @if ($story->elements)
            <div class="row mb-4">
                <h3 class="mt-4">Слайды</h3>
                <div class="row">
                    <div class="col-lg-12 mb-3">
                        <ul class="images">
                            @foreach($story->elements as $e)
                                <li>
                                    <img src="{{ URLHelper::transform($e[0]) }}"/>
                                    <i class="fa fa-times-circle" aria-hidden="true" title="Удалить"></i>
                                    <input type="hidden" name="active_images[]" value="{{ $e[0] }}">
                                    <div class="button-text" data-bs-toggle="modal" data-bs-target="#editSlideModal">
                                        <span>{{ $e[1] }}</span>
                                        <input class="link" type="hidden" name="active_links[]" value="{{ $e[2] }}">
                                        <input class="text" type="hidden" name="active_links_text[]" value="{{ $e[1] }}">
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

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

    <div class="modal fade" id="editSlideModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Параметры кнопки</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label for="slide-link" class="col-form-label">Ссылка:</label>
                            <input type="text" class="form-control" id="slide-link">
                        </div>
                        <div class="mb-3">
                            <label for="slide-text" class="col-form-label">Текст кнопки:</label>
                            <input type="text" class="form-control" id="slide-text">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button id="slideApplyButton" data-bs-dismiss="modal" type="button" class="btn btn-primary">Применить</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            moment.locale('ru');

            const editSlideModal = document.getElementById('editSlideModal');
            editSlideModal.addEventListener('show.bs.modal', (event) => {
                const button = $(event.relatedTarget);
                const iLink = button.find(".link");
                const iText = button.find(".text");

                const sLink = $("#slide-link");
                const sText = $("#slide-text");

                sLink.val(iLink.val());
                sText.val(iText.val());

                $('#slideApplyButton').unbind().on('click', function() {
                    iLink.val(sLink.val());
                    iText.val(sText.val());

                    button.find("span").html(sText.val());
                });
            });

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
