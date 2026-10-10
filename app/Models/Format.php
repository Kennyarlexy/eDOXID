<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Format extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'document_format',
        'document_format_name',
        'document_format_size',
        'is_primary',
        'is_pembimbing_required',
        'is_penguji_required',
        'is_tata_usaha_required',
        'is_skill_group_required',
        'is_need_trial_schedule',
        'give_access_to_mahasiswa',
        'maks_lecturer_selected_by_student',
        'if_approved_next_submission_using_previous_verificators',
        'is_all_lecturers_can_consultations',
        'type_id',
        'is_pembimbing_need_to_change_trial_status',
        'is_penguji_need_to_change_trial_status',
    ];

    public function type()
    {
        return $this->belongsTo(Type::class, 'type_id', 'id');
    }

    public function required_files()
    {
        return $this->hasMany(RequiredFile::class, 'format_id', 'id');
    }

    public function required_values()
    {
        return $this->hasMany(RequiredValue::class, 'format_id', 'id');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class, 'format_id', 'id');
    }

    public function get_type_access_permission()
    {
        return $this->hasMany(GetTypeAccessPermission::class, 'format_id', 'id');
    }
}
