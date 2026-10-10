<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    use HasFactory;

    const NAME_PROPOSAL = 'Proposal';
    const NAME_SIDANG_HASIL = 'Sidang Hasil';
    const NAME_SIDANG_AKHIR = 'Sidang Akhir';

    protected $fillable = [
        'name',
        'slug',
        'color',
        'description',
        'status',
    ];

    public function formats()
    {
        return $this->hasMany(Format::class, 'type_id', 'id');
    }

    public function getTypeAccessPermissions()
    {
        return $this->hasMany(GetTypeAccessPermission::class, 'type_id', 'id');
    }

    public function mahasiswaTypeAccessPermissions()
    {
        return $this->belongsToMany(User::class, 'mahasiswa_type_access_permissions', 'type_id', 'mahasiswa_id');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class, 'type_id', 'id');
    }
}
