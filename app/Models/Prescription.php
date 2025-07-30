<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Wildside\Userstamps\Userstamps;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasClient;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prescription extends Model
{
    use HasFactory;
    use Userstamps;
    use SoftDeletes;
    use HasClient;
    protected $hidden = [
        'deleted_by',
        'deleted_at',
        'updated_by',
    ];

    protected $casts = [
        'doc_files' => 'array',
    ];

    public function person(): BelongsTo{
        return $this->belongsTo(Person::class);
    }
}
