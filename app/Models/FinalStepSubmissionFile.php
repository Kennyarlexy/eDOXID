<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinalStepSubmissionFile extends Model
{
    use HasFactory;

    const FINAL_STEP_FILE_TYPE_NAME_FINAL_THESIS = 'final-thesis';
    const FINAL_STEP_FILE_TYPE_NAME_VALIDITY_SHEET = 'validity-sheet';

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'final_thesis_file_name',
        'final_thesis_file_url',
        'final_thesis_file_status',
        'final_thesis_file_upload_at',
        'validity_sheet_file_name',
        'validity_sheet_file_url',
        'validity_sheet_file_status',
        'validity_sheet_file_upload_at',
        'mahasiswa_id',
    ];

    public $timestamps = false;
}
