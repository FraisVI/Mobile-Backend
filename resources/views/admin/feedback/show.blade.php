@extends('admin.layout')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">{{ $title }}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
        </div>
    </div>

    <style>
        .messages {
            display: flex;
            flex-direction: column;
        }

        .messages > div > div {
            position: relative;
            padding: 10px 15px 10px 15px;
            background: #fafafa;
            border-radius: 10px;
            width: fit-content;
            margin-bottom: 15px;
            display: flex;
            flex-wrap: nowrap;
            flex-direction: row;
            align-content: center;
            align-items: center;
            justify-content: center;
            min-width: 40%;
        }

        .messages > div > div > div:nth-child(3) {
            width: 100%;
        }

        .messages > .own {
            display: flex;
            justify-content: flex-end;
        }

        .messages > .own > div {
            flex-direction: row-reverse;
            background: #efefef;
        }

        .messages > div > div > div:nth-child(2) {
            margin-right: 15px;
        }

        .messages > .own > div > div:nth-child(2) {
            margin-right: 0px;
            margin-left: 15px;
        }

        .messages > div > div > div:nth-child(2) img {
            border-radius: 25px;
            width: 40px;
            height: 40px;
        }

        .file-border {
            padding: 5px 10px 5px 10px;
            background: #eeeeee;
            margin: 5px 5px 5px 0px;
            border-radius: 5px;
        }

        .delete-message-button {
            display: none;
            position: absolute;
            right: 10px;
            font-size: 12px;
            top: 5px;
            cursor: pointer;
        }

        .messages > div > div:hover .delete-message-button {
            display: block;
        }

    </style>

    <div class="row">
        <div class="col-md-6 messages">
            <?php /** @var \App\Models\Feedback[] $messages */ ?>
            @foreach($messages as $m)
                <div class="{{ $m->from_user != 1 ? 'own' : '' }}">
                    <div>
                        <span class="delete-message-button" data-message-id="{{ $m->id }}" title="Удалить"><i class="fa fa-trash-o" aria-hidden="true"></i></span>
                        <div>
                            @if ($m->from_user == 1)
                                <img src="https://ui-avatars.com/api/?length=2&amp;background=random&amp;name={{ $user->shortFio() }}">
                            @else
                                <img src="/img/fb-avatar.png">
                            @endif
                        </div>
                        <div>
                            {!! nl2br(e($m->message)) !!}<br />
                            @if (isset($m->options['callback']))<span class="badge bg-danger">Запросил обратный звонок</span><br />@endif
                            @if (isset($m->options['file']))
                                <div class="file-border">
                                    <i class="fa fa-file-image-o" aria-hidden="true"></i>
                                    <a href="/admin/feedback/file/{{ $m->options['file'] }}">{{ $m->options['filename'] }}</a>
                                </div>
                            @endif
                            <small style="color: #919191;">{{ $m->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <form id="feedback-form" class="form-floating needs-validation" action="{{ route('feedback.send', [$user->id]) }}" method="post" novalidate>
        @csrf

        <div class="row mb-2">
            <div class="col-lg-6" style="background: #f8f9fa; padding: 15px; border-radius: 15px; border: 1px solid #d4dde7;">
                <strong>Пользователь:</strong><br />
                {{ $user->fio() }} ( <a href="tel:+7{{ $user->phone }}">+7{{ $user->phone }}</a> ) был активен {{ $session?->lastused->diffForHumans() ?? 'неизвестно' }}<br /><br />
                <small>
                    Версия: {{ $session?->version ?? 'неизвестно' }}<br />
                    Первый вход: {{ $user->created_at->format('d.m.Y') }} ( {{ $user->created_at->diffForHumans() }} )<br />
                    UAgent: {{ $session?->uagent ?? 'неизвестно' }}
                </small>
            </div>
        </div>

        <div class="row mb-4">
            <h3 class="mt-4">Ответить пользователю</h3>
                <div class="col-lg-6 mb-3">
                <textarea class="form-control" rows="8" name="message" minlength="3"
                  placeholder=""></textarea>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-2">
                <button class="w-100 btn btn-lg btn-dark">Отправить</button>
            </div>
        </div>

        <br /><br />
    </form>

    <script>
        $(document).ready(function() {
            moment.locale('ru');

            $("#feedback-form").submit(function (event) {
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

            $(".delete-message-button").on('click', function() {
                const messageId = $(this).data('message-id');

                if (confirm("Подтвердите удаление сообщения")) {
                    $.post( "/admin/feedback/destroy/" + messageId, { _method: 'delete' } );
                    $(this).parent().parent().fadeOut('slow');
                }
            });

            $(document).scrollTop($(document).height());
        });
    </script>
@endsection
