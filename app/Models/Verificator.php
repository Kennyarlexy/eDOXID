<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Verificator extends Model
{
    use HasFactory;

    const TYPE_PEMBIMBING = 'Pembimbing';
    const TYPE_PENGUJI = 'Penguji';

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    const FINAL_STATUS_PENDING = 'pending';
    const FINAL_STATUS_APPROVED = 'approved';
    const FINAL_STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'type',
        'status',
        'final_status',
        'note',
        'is_read_by_dosen',
        'user_id',
        'submission_id',
        'is_need_to_change_trial_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function submission()
    {
        return $this->belongsTo(Submission::class, 'submission_id');
    }
}
