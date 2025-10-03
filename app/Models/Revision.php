<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Revision extends Model
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
        'puskesmas_id',
        'jenis_dokumen_id',
        'catatan',
        'is_resolved',
        'resolved_at',
        'is_verified',
        'verified_at',
        'revised_by',
        'resolved_by',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'jenis_dokumen_id' => 'integer',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'revised_by' => 'integer',
        'resolved_by' => 'integer',
    ];

    /**
     * Get the puskesmas that owns the Revision
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function puskesmas(): BelongsTo
    {
        return $this->belongsTo(Puskesmas::class, 'puskesmas_id', 'id');
    }

    /**
     * Get the jenisDokumen that owns the Revision
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function jenisDokumen(): BelongsTo
    {
        return $this->belongsTo(JenisDokumen::class, 'jenis_dokumen_id', 'id');
    }

    /**
     * Get the user that revised the Revision
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function revisedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revised_by', 'id');
    }

    /**
     * Get the user that resolved the Revision
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by', 'id');
    }
}
