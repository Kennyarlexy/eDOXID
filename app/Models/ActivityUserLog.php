<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityUserLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'details',
        'user_id'
    ];
}
