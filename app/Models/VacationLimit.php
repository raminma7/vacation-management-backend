<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VacationLimit extends Model
{
    use HasFactory;

    protected $fillable = [
        'yearly_limit',
        'monthly_limit',
    ];
}
