<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubmissionRequiredFile extends Model
{
    use HasFactory;

    const STATUS_PENDING = "pending";
    const STATUS_APPROVED = "approved";
    const STATUS_REJECTED = "rejected";

    protected $fillable = [
        'filename',
        'url',
        'status',
        'required_file_id',
        'submission_id',
        'is_verificator_can_edit'
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class, 'submission_id');
    }

    public function required_file()
    {
        return $this->belongsTo(RequiredFile::class, 'required_file_id');
    }
}
