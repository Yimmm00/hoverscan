<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bridge extends Model
{
    use HasFactory;

    // Tell Laravel this specific table does not track raw Eloquent timestamps
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

    /**
     * Get the defect records matching this bridge name constraint.
     */
    public function defectRecords(): HasMany
    {
        return $this->hasMany(DefectRecord::class, 'bridge_name', 'name');
    }
}