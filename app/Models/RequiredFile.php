<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequiredFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'note',
        'type_file',
        'format_id',
        'is_verificator_can_edit'
    ];
}
