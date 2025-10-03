<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $fillable = ['name', 'default_credits'];

    public function employeeLeaves()
    {
        return $this->hasMany(EmployeeLeave::class);
    }
}