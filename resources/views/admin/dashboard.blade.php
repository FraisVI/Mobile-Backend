@extends('admin.layout')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">{{ $title }}</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
        </div>
    </div>


    <div class="row">
        <div class="col-md-12 info-card-container">
            <div class="info-card">
                <div class="left">
                    <div class="name">Всего зарегистрировано</div>
                    <div class="value">{{ number_format($usersTotal, 0, ',', ' ') }}</div>
                </div>

                <div class="right">
                    <div class="icon">
                        <i class="fa fa-users" aria-hidden="true"></i>
                    </div>
                </div>
            </div>

            <div class="info-card">
                <div class="left">
                    <div class="name">С токенами для пушей</div>
                    <div class="value">{{ number_format($usersWithTokens, 0, ',', ' ') }}</div>
                </div>

                <div class="right">
                    <div class="icon">
                        <i class="fa fa-bell" aria-hidden="true"></i>
                    </div>
                </div>
            </div>

            <div class="info-card">
                <div class="left">
                    <div class="name">Android</div>
                    <div class="value">{{ $android }} <small>{{ $pct }}%</small></div>
                </div>

                <div class="right">
                    <div class="icon">
                        <i class="fa fa-android" aria-hidden="true"></i>
                    </div>
                </div>
            </div>

            <div class="info-card">
                <div class="left">
                    <div class="name">iOS</div>
                    <div class="value">{{ $ios }} <small>{{ 100 - $pct }}%</small></div>
                </div>

                <div class="right">
                    <div class="icon">
                        <i class="fa fa-apple" aria-hidden="true"></i>
                    </div>
                </div>
            </div>

            <div class="info-card">
                <div class="left">
                    <div class="name">SMS Баланс</div>
                    <div class="value">{{ $sms_balance }}</div>
                </div>

                <div class="right">
                    <div class="icon">
                        <i class="fa fa-rub" aria-hidden="true"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .info-card-container {
            display: flex;
        }

        .info-card {
            width: 260px;
            height: 80px;
            border-radius: 20px;
            box-shadow: rgba(0, 0, 0, 0.16) 0px 1px 4px;
            padding: 10px;
            margin-bottom: 15px;
            margin-right: 15px;

            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .info-card .left {
            padding-left: 5px;
        }

        .info-card .icon {
            color: #333;
            font-size: 32px;
        }

        .info-card .name {
            font-size: 12px;
        }

        .info-card .value {
            font-weight: bold;
            font-size: 20px;
        }

        .info-card .value small {
            font-size: 12px;
            color: #999;
        }
    </style>
@endsection
