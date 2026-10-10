<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    const ROLE_ADMIN = 'admin';
    const ROLE_BAGIAN_ADMINISTRASI = 'bagian administrasi';
    const ROLE_DOSEN = 'dosen';
    const ROLE_MAHASISWA = 'mahasiswa';

    const SIGNATURE_PENDING = 'pending';
    const SIGNATURE_APPROVED = 'approved';
    const SIGNATURE_REJECTED = 'rejected';

    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
        'unique_id',
        'phone_number',
        'position',
        'role',
        'google_drive_folder_id',
        'signature_image',
        'signature_status',
        'profile_image',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function mahasiswa_type_access_permissions()
    {
        return $this->belongsToMany(Type::class, 'mahasiswa_type_access_permissions', 'mahasiswa_id', 'type_id');
    }

    // Bagian Administrasi and dosen relationship
    public function verificators()
    {
        return $this->belongsToMany(Submission::class, 'verificators', 'user_id', 'submission_id')->withPivot(['status', 'type'])->withTimestamps();
    }

    // mahasiswa submissions
    public function submissions()
    {
        return $this->hasMany(Submission::class, 'student_id', 'id');
    }

    public function final_step_submission()
    {
        return $this->hasOne(FinalStepSubmissionFile::class, 'student_id', 'id');
    }
}
