<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AccountDeletionInfo
 *
 * @property int $user_id
 * @property int $fail_count
 * @property \Illuminate\Support\Carbon $date_sent
 * @method static AccountDeletionInfo|null find(int $id)
 * @mixin \Eloquent
 */

class AccountDeletionInfo extends Model
{
    protected $table = 'account_deletion_info';
    public $timestamps = false;

    protected $primaryKey = 'user_id';

    protected $casts = [
        'date_sent' => 'datetime:Y-m-d H:i:s',
    ];
}
