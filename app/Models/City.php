<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class City extends Model
{
    public $timestamps = false;

    protected $fillable = ['county_id', 'zip_code', 'name'];

    public function county(): BelongsTo
    {
        return $this->belongsTo(County::class);
    }
}
