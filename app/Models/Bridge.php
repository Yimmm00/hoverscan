<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bridge extends Model
{
    use HasFactory;

    // ⚡ FIX: Tell Laravel this specific table does not track raw Eloquent timestamps
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'district',
        'location_coords',
        'total_anomalies'
    ];
}