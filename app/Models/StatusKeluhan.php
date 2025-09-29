<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StatusKeluhan extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'status',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
    ];

    /**
     * Get all of the keluhan for the StatusKeluhan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function keluhan(): HasMany
    {
        return $this->hasMany(Keluhan::class, 'status_id', 'id');
    }
}
