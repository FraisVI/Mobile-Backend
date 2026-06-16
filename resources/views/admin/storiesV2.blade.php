@extends('admin.layout')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">{{ $title }}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
        </div>
    </div>

    <style>
        #stories-table tbody tr td:nth-child(3) img {
            max-width: 64px;
            max-height: 128px;
        }

        .fa {
            transition: all 0.5s ease;
        }

        .fa-arrows {
            cursor: grab;
        }
    </style>

    <a href="{{ route('storiesV2.create') }}" class="btn btn-dark mb-3">
        <i style="padding-right: 5px;" class="fa fa-fw fa-plus-circle" aria-hidden="true"></i> Создать story
    </a>

    <button class="reorder-button btn btn-secondary mb-3"><i class="fa fa-sort" aria-hidden="true"></i> Сохранить порядок сортировки</button>

    <table id="stories-table" class="table">
        <thead>
        <tr>
            <th></th>
            <th>ID</th>
            <th></th>
            <th>Заголовок</th>
            <th>Длительность</th>
            <th>Опубликован</th>
            <th>Управление</th>
        </tr>
        </thead>
        <tbody class="table-group-divider">
        <?php /** @var \App\Models\StoriesV2 $stories */ ?>
        @foreach($stories as $s)
            <tr>
                <td><i class="fa fa-arrows" aria-hidden="true"></i></td>
                <td>
                    {{ $s->id }}
                </td>
                <td><img src="{{ URLHelper::transform($s->preview) }}" /></td>
                <td>
                    {{ $s->title }} <br />
                    <small><a target="_blank" href="{{ $s->href }}">{{ $s->href }}</a></small>
                </td>
                <td>{{ $s->duration / 1000 }} сек.</td>
                <td>{{ $s->published == 1 ? '+' : '' }}</td>
                <td>
                    <form onSubmit="if(!confirm('Вы уверены, что хотите удалить запись?')){return false;}" action="{{ route('storiesV2.destroy', [$s->id]) }}" method="post">
                        @csrf
                        @method('DELETE')
                        <div class="btn-group" role="group">
                            <a href="{{ route('storiesV2.edit', [$s->id]) }}" class="btn btn-primary btn-sm">Изменить</a>
                            <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                        </div>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <script>
        $(document).ready(function() {
            $('#stories-table tbody').sortable({
                swapThreshold: 0.30,
                animation: 150,
                handle: '.fa-arrows'
            });

            $('.reorder-button').click(function () {
                let order = [];
                let values = $('#stories-table tbody tr td:nth-child(2)');
                values.each(function() {
                    let id = parseInt($(this).html().trim());
                    order.push(id);
                });

                const button = $(this);
                const icon = $(this).find('.fa');

                icon.removeClass('fa-sort');
                icon.addClass('fa-spinner fa-pulse');
                button.prop('disabled', true);

                $.post({
                    url: '{{ route('storiesV2.reorder') }}',
                    data: { order: order },
                    dataType: 'json',
                }).done(function() {

                }).fail(function() {
                    alert( "Произошла ошибка при сохранении порядка элементов." );
                }).always(function() {
                    icon.removeClass('fa-spinner fa-pulse');
                    icon.addClass('fa-sort');
                    button.prop('disabled', false);
                });

            });
        });
    </script>
@endsection
