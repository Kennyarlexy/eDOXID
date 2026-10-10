<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmissionRequiredValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'value',
        'required_value_id',
        'submission_id',
        'verificator_id'
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class, 'submission_id');
    }

    public function verificator()
    {
        return $this->belongsTo(Verificator::class, 'verificator_id');
    }

    public function required_value()
    {
        return $this->belongsTo(RequiredValue::class, 'required_value_id');
    }
}
