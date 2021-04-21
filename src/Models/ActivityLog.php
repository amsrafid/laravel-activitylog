<?php

namespace Amsrafid\ActivityLog\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'log_name',
        'mode',
        'description',
        'model',
        'primary_id',
        'user_id',
        'properties'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getPropertiesAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }
}
