<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Shift extends Model
{
    protected $fillable = [
        'employee_id',
        'shift_library_id',
        'shift_type',
        'days',
        'start_time',
        'end_time',
        'break_time',
    ];

    protected $casts = [
        'days' => 'array',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class);
    }

    public function shiftLibrary()
    {
        return $this->belongsTo(ShiftLibrary::class);
    }

    /**
     * Get the shift time range from library or fallback to direct values
     */
    public function getTimeRangeAttribute()
    {
        if ($this->shiftLibrary) {
            return $this->shiftLibrary->time_range;
        }
        return \Carbon\Carbon::parse($this->start_time)->format('h:i A') . ' - ' . \Carbon\Carbon::parse($this->end_time)->format('h:i A');
    }
}