<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisDokumen extends Model
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
        'nama_dokumen',
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
     * Get all of the revisions for the JenisDokumen
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function revisions(): HasMany
    {
        return $this->hasMany(Revision::class, 'jenis_dokumen_id', 'id');
    }
}
