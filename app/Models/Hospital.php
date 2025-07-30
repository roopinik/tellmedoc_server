<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Wildside\Userstamps\Userstamps;
use App\Traits\HasClient;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

class Hospital extends Model
{
    use Userstamps;
    use HasClient;
    use HasFactory;
    use HasTranslations;

    public $translatable = ['name'];

    public function specializations(): BelongsToMany
    {
        return $this->belongsToMany(Specialization::class, "hospital_specializations", "hospital_id", "specialization_id");
    }
}
