<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkillGroupLecturer extends Model
{
    use HasFactory;

    // Ketua, Sekretaris dan Anggota
    const KETUA = 'Ketua';
    const SEKRETARIS = 'Sekretaris';
    const ANGGOTA = 'Anggota';

    protected $fillable = [
        'position',
        'lecturer_id',
        'skill_group_id',
    ];

    public function skill_group()
    {
        return $this->belongsTo(SkillGroup::class);
    }

    public function lecturer()
    {
        return $this->belongsTo(User::class, 'lecturer_id', 'id');
    }
}
