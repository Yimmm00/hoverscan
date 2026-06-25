<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DefectRecord extends Model
{
    protected $fillable = [
        'dataset_id', 'bridge_name', 'defect_class', 'severity', 
        'confidence_score', 'image_path', 'humidity', 'temperature', 'bbox_coordinates'
    ];

    protected $casts = [
        'bbox_coordinates' => 'array',
        'confidence_score' => 'float',
        'created_at' => 'datetime:Y-m-d H:i',
    ];

    public function bridge(): BelongsTo
    {
        return $this->belongsTo(Bridge::class, 'bridge_name', 'name');
    }
}