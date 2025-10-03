<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Shift extends Model
{
    protected $fillable = [
        'employee_id', 'shift_type', 'days',
        'start_time', 'end_time', 'break_time',
    ];

    protected $casts = [
        'days' => 'array',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class);
    }
}