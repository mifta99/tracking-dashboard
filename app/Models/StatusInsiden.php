<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StatusInsiden extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'status_insiden';

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
     * Get all of the insiden for the StatusInsiden
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function insiden(): HasMany
    {
        return $this->hasMany(Insiden::class, 'status_id', 'id');
    }
}
