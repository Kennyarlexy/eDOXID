<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grading extends Model
{
    use HasFactory;

    protected $fillable = [
        'maksimal_score',
        'minimal_score',
        'grade',
        'description',
        'submission_status'
    ];
}
