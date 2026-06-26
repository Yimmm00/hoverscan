<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes; // ⚡ 1. Import

class DefectRecord extends Model
{
    use SoftDeletes; // ⚡ 2. Initialize

    public $timestamps = false; 
    
    // Tell Eloquent that deleted_at is your lone active mutation tracker
    const DELETED_AT = 'deleted_at'; 

    protected $fillable = [
        'dataset_id', 'bridge_name', 'defect_class', 'severity', 
        'confidence_score', 'image_path', 'humidity', 'temperature', 'bbox_coordinates'
    ];

    protected $casts = [
        'bbox_coordinates' => 'array',
        'confidence_score' => 'float',
        'created_at' => 'datetime:Y-m-d H:i',
        'deleted_at' => 'datetime', // ⚡ 3. Cast explicitly
    ];

    public function bridge(): BelongsTo
    {
        return $this->belongsTo(Bridge::class, 'bridge_name', 'name');
    }
}