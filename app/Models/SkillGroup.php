<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkillGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status'
    ];

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function skill_group_lecturers()
    {
        return $this->hasMany(SkillGroupLecturer::class);
    }
}
