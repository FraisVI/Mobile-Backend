<?php

namespace App\Models;

use Carbon\Carbon;

class ProfileData
{
    var string $FIO;
    var string $Gender;

    var bool $Blocked;
    var bool $ClientNotFound;
    var Carbon $Birthdate;

    var int $BonusCount;
    var int $BonusRate;

    var string $ClientCardID;

    function __construct(Object $json) {
        $this->FIO = $json->FIO;
        $this->Gender = $json->Gender;

        // "Birthdate": "1982-01-19T00:00:00",
        // d.m.Y
        $this->Birthdate = Carbon::parse(substr($json->Birthdate, 0, 10));

        $this->Blocked = $json->Blocked;
        $this->ClientNotFound = $json->ClientNotFound;

        $this->ClientCardID = $json->ClientCardID;
        $this->BonusCount = $json->BonusCount;
        $this->BonusRate = $json->BonusRate;
    }
}
