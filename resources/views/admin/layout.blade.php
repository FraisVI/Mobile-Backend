<!doctype html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=1280">
        <title>{{ $title }}</title>
        <meta name="theme-color" content="#393186" />
        <meta name="_token" content="{{ csrf_token() }}" />

        <link href="https://fonts.googleapis.com/css?family=Roboto+Slab:400,700&amp;subset=cyrillic" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"
              integrity="sha256-eZrrJcwDc/3uDhsdt61sL2oOBY362qM3lon1gyExkL0=" crossorigin="anonymous" />

        <script src="https://code.jquery.com/jquery-3.5.1.min.js"
            integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>

        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
                integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.2/css/bootstrap.min.css"
              integrity="sha512-CpIKUSyh9QX2+zSdfGP+eWLx23C8Dj9/XmHjZY2uDtfkdLGo0uY12jgcnkX9vXOgYajEKb/jiw67EYm+kBf+6g=="
              crossorigin="anonymous" referrerpolicy="no-referrer" />

        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.2/js/bootstrap.bundle.min.js"
                integrity="sha512-BOsvKbLb0dB1IVplOL9ptU1EYA+LuCKEluZWRUYG73hxqNBU85JBIBhPGwhQl7O633KtkjMv8lvxZcWP+N3V3w=="
                crossorigin="anonymous" referrerpolicy="no-referrer"></script>

        <link rel="stylesheet" href="/css/cp-style.css{{ env('APP_DEBUG') ? "?" . rand() : "?v=1" }}">

        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment-with-locales.min.js"
                integrity="sha512-LGXaggshOkD/at6PFNcp2V2unf9LzFq6LE+sChH7ceMTDP0g2kn6Vxwgg7wkPP7AAtX+lmPqPdxB47A0Nz0cMQ=="
                crossorigin="anonymous" referrerpolicy="no-referrer"></script>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.33/moment-timezone-with-data.min.js"
                integrity="sha512-rjmacQUGnwQ4OAAt3MoAmWDQIuswESNZwYcKC8nmdCIxAVkRC/Lk2ta2CWGgCZyS+FfBWPgaO01LvgwU/BX50Q=="
                crossorigin="anonymous" referrerpolicy="no-referrer"></script>

        <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.css"
              integrity="sha512-ngQ4IGzHQ3s/Hh8kMyG4FC74wzitukRMIcTOoKT3EyzFZCILOPF0twiXOQn75eDINUfKBYmzYn2AA8DkAk8veQ=="
              crossorigin="anonymous" referrerpolicy="no-referrer" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.js"
                integrity="sha512-6F1RVfnxCprKJmfulcxxym1Dar5FsT/V2jiEUvABiaEiFWoQ8yHvqRM/Slf0qJKiwin6IDQucjXuolCfCKnaJQ=="
                crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"
                integrity="sha512-Eezs+g9Lq4TCCq0wae01s9PuNWzHYoCMkE97e2qdkYthpI0pzC3UGB03lgEHn2XM85hDOUF6qgqqszs+iXU4UA=="
                crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdn.jsdelivr.net/npm/jquery-sortablejs@latest/jquery-sortable.js"></script>

        <script>
            $( document ).ready(function() {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                    }
                });

                $(".alert-success").fadeTo(2000, 500).slideUp(500, function(){
                    $(".alert-success").slideUp(500);
                });


                /*$('.mobile-menu-toggler').click(function () {
                    $('.mobile-menu').addClass('show');
                    $('.mobile-menu-overlay').show();

                    $('body').css('overflow-y', 'hidden');
                });

                $('.mobile-menu-overlay, .menu-close-button').click(function () {
                    $('.mobile-menu').removeClass('show');
                    $('.mobile-menu-overlay').hide();

                    $('body').css('overflow-y', 'auto');
                });

                $('.mobile-menu-tree .parent').click(function () {
                    $(this).toggleClass('expanded');
                });*/
            });
        </script>
    </head>
    <body>
        <nav class="navbar navbar-dark bg-dark flex-md-nowrap p-0 shadow sticky-top">
            <a class="navbar-brand col-md-3 col-lg-2 mr-0 px-3" style="z-index: 101; background: #262b2f;" href="{{ $prefix }}/">Моб. приложение Каприз</a>
            <button class="navbar-toggler position-absolute d-lg-none collapsed" type="button" data-toggle="collapse"
                    data-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="header-panel-row mx-2">
            </div>

            <ul class="navbar-nav px-3">
                <li class="nav-item text-nowrap">
                    <!--<a class="nav-link" href="/admin/logout">Выход</a>-->
                </li>
            </ul>
        </nav>

        <div class="container-fluid">
            <div class="row">
                <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-lg-block bg-light sidebar collapse">
                    <div class="sidebar-sticky pt-3">
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 text-muted">
                            <span>Меню</span>
                        </h6>

                        <ul class="nav flex-column mb-4">
                            @foreach($menu as $k => $v)
                                <li class="nav-item">
                                    <a @class(['nav-link' => true, 'active' => (Str::startsWith($path . '/', $k . '/') && $path != '/') || ($path == '/' && $k == '/')]) href="{{ url(rtrim($prefix, '/') . '/' . ltrim($k, '/')) }}">
                                        <i class="fa {{ $menu_icons[$k] }}" aria-hidden="true"></i>
                                        {{ $v }}
                                        @if ($k == '/feedback' && $feedback_counter > 0)
                                            <span class="badge rounded-pill bg-danger">{{ $feedback_counter }}</span>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </nav>

                <main role="main" class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                    @if(Session::has('success-message'))
                        <p class="alert alert-success mt-4">{!! Session::get('success-message') !!}</p>
                    @endif

                    @if(Session::has('error-message'))
                        <p class="alert alert-danger mt-4">{!! Session::get('error-message') !!}</p>
                    @endif

                    @yield('content')
                </main>
            </div>
        </div>
    </body>
</html>
