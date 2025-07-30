<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Wildside\Userstamps\Userstamps;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Specialization;
use Spatie\Translatable\HasTranslations;

class Client extends Model
{
    use HasFactory;
    use Userstamps;
    use SoftDeletes;
    use HasTranslations;

    public $translatable = ['name'];
    protected $fillable = ['notify_appointment_booking'];
    protected $casts = [
        'notify_appointment_booking' => 'boolean',
    ];
    protected $hidden = [
        'deleted_by',
        'deleted_at',
        'updated_by',
    ];

    public function specializations(): BelongsToMany
    {
        return $this->belongsToMany(Specialization::class);
    }
}
