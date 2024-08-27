<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class VerifyCode extends Model
{

    use HasFactory;

    use Notifiable;

    use HasApiTokens;

    protected $fillable = [
        'mobile_number',
        'creating_time',
        'expire_time',
        'code'
    ];

}
