<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GetTypeAccessPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'type_id',
        'access_type_id',
        'format_id'
    ];

    public function type()
    {
        return $this->belongsTo(Type::class, 'type_id', 'id');
    }

    public function access_type()
    {
        return $this->belongsTo(Type::class, 'access_type_id', 'id');
    }

    public function format()
    {
        return $this->belongsTo(Format::class, 'format_id', 'id');
    }
}
