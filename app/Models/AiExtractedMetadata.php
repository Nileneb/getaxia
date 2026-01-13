<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AiExtractedMetadata extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'ai_extracted_metadata';

    protected $fillable = [
        'entity_type',
        'entity_id',
        'raw_text',
        'extracted_data_json',
    ];

    protected $casts = [
        'extracted_data_json' => 'array',
    ];

    public function entity(): MorphTo
    {
        return $this->morphTo(null, 'entity_type', 'entity_id');
    }
}
