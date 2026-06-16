@extends('admin.layout')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">{{ $title }}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
        </div>
    </div>

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs5/dt-1.13.1/fh-3.3.1/datatables.min.css"/>
    <script type="text/javascript" src="https://cdn.datatables.net/v/bs5/dt-1.13.1/fh-3.3.1/datatables.min.js"></script>
    <style>
        .dataTable td, .dataTable th {
            white-space: nowrap;
        }

        .dataTable thead th {
            vertical-align: top !important;
        }
    </style>

    <table id="rating-table" class="table table-sm table-striped">
        <thead>
            <tr>
                <th>Hash</th>
                <th>Пользователь</th>
                <th>Оценка</th>
                <th>Сообщение</th>
                <th>Создано</th>
                <th>Получено</th>
            </tr>
        </thead>
        <tbody>
        <?php /** @var \App\Models\Rating[] $ratings */ ?>
        @foreach($ratings as $r)
            <tr>
                <td>{{ $r->hash }}</td>
                <td>{{ $r->user->fio() }} (+7{{ $r->user->phone }})</td>
                <td>{{ $r->rating }}</td>
                <td>{{ $r->message }}</td>
                <td data-order="{{ $r->created_at->timestamp }}">{{ $r->created_at->diffForHumans() }}</td>
                <td data-order="{{ $r->updated_at->timestamp }}">{{ $r->updated_at->diffForHumans() }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <script>
        $( document ).ready(function() {
            let table = $('#rating-table').DataTable({
                fixedHeader: true,
                "order": [[ 1, "desc" ]],
                "language": {
                    "url": "/js/Russian.json"
                },
                scrollY: 'calc(100vh - 260px)',
                paging: true,
                lengthMenu: [
                    [23, 50, 100, -1],
                    [23, 50, 100, 'Все'],
                ],
                "columnDefs": [
                ]
            });
        });
    </script>

@endsection
