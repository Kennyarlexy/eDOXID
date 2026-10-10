<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmissionPresence extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'unique_id',
        'keterangan',
        'signature_image',
        'submission_id',
        'input_by_user'
    ];
}
