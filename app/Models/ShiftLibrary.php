<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShiftLibrary extends Model
{
    use HasFactory;

    protected $table = 'shift_libraries';

    protected $fillable = [
        'shift_name',
        'start_time',
        'end_time',
        'break_time',
        'description',
    ];

    public function employeeShifts()
    {
        return $this->hasMany(Shift::class, 'shift_library_id');
    }

    /**
     * Format time for display
     */
    public function getTimeRangeAttribute()
    {
        return \Carbon\Carbon::parse($this->start_time)->format('h:i A') . ' - ' . \Carbon\Carbon::parse($this->end_time)->format('h:i A');
    }
}
