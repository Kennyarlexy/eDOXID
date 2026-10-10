<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSED = 'processed';
    const STATUS_APPROVED = 'approved';
    const STATUS_REPEATED = 'repeated';
    const STATUS_REJECTED = 'rejected';

    const GRADE_STATUS_REPEATED = 'repeated';
    const GRADE_STATUS_APPROVED = 'approved';
    const GRADE_STATUS_REJECTED = 'rejected';

    const RESULT_PENDING = 'pending';
    const RESULT_APPROVED = 'approved';
    const RESULT_APPROVED_WITH_FIXED = 'approved_with_fixed';
    const RESULT_REJECTED = 'rejected';
    const RESULT_REPEATED = 'repeated';

    protected $fillable = [
        'title',
        'description',
        'status',
        'schedule',
        'schedule_id',
        'schedule_request',
        'room',
        'record_url',
        'grade',
        'score',
        'grade_description',
        'grade_status',
        'student_id',
        'type_id',
        'skill_group_id',
        'format_id',
        'document_format',
        'academic_year',
        'amount_sks_now',
        'ipk_now',
        'is_read_by_tata_usaha',
        'is_ready_to_be_processed',
        'final_document_url',
        'final_document_name',
        'final_document_created_at',
        'semester',
        'spota_submission_id',
        'ready_at',
        'result_status',
        'recomendation_repeated_schedule',
        'verificator_id_who_submit_result',
        'updated_submit_result_at',
        'presence_public_url_open_datetime',
        'presence_public_url_close_datetime',
        'presence_public_url_status',
        'updated_at',
        'created_at',
        'next_title_recomendation',
        'file_sk_url',
        'file_sk_name',
        'file_sk_created',
        'bypass_pengajuan_kaprodi',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function type()
    {
        return $this->belongsTo(Type::class, 'type_id');
    }

    public function format()
    {
        return $this->belongsTo(Format::class, 'format_id');
    }

    public function skill_group()
    {
        return $this->belongsTo(SkillGroup::class, 'skill_group_id');
    }

    public function consultations()
    {
        return $this->hasMany(SubmissionConsultation::class, 'submission_id');
    }

    public function required_files()
    {
        return $this->hasMany(SubmissionRequiredFile::class, 'submission_id');
    }

    public function required_values()
    {
        return $this->hasMany(SubmissionRequiredValue::class, 'submission_id');
    }

    public function verificators()
    {
        return $this->belongsToMany(User::class, 'verificators', 'submission_id', 'user_id')->withPivot(['id', 'status', 'final_status', 'type', 'note'])->withTimestamps();
    }

    public function presences()
    {
        return $this->hasMany(SubmissionPresence::class, 'submission_id');
    }
}
