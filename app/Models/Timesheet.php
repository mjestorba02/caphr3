<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Timesheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'from_date',
        'to_date',
        'day',
        'start_time',
        'end_time',
        'lunch_break',
        'hours_worked',
        'overtime',
        'position',
        'notes',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function trackings()
    {
        return $this->hasMany(TimeTracking::class, 'employee_id', 'employee_id');
    }

    public function getComputedHoursWorkedAttribute()
    {
        return round(
            $this->trackings()
                ->whereBetween('date', [
                    Carbon::parse($this->from_date)->startOfDay(),
                    Carbon::parse($this->to_date)->endOfDay(),
                ])
                ->sum('total_hours'),
            2
        );
    }

    public function getComputedOvertimeAttribute()
    {
        return round(
            $this->trackings()
                ->whereBetween('date', [
                    Carbon::parse($this->from_date)->startOfDay(),
                    Carbon::parse($this->to_date)->endOfDay(),
                ])
                ->sum('overtime'),
            2
        );
    }
}