<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\MaintenanceMaster;

class Maintenance extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'maintenance';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'maintenance_master_id',
        'equipment_id',
        'puskesmas_id',
        'tgl_maintenance',
        'dokumentasi',
        'berita_acara',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'maintenance_master_id' => 'integer',
        'equipment_id' => 'integer',
        'tgl_maintenance' => 'date',
    ];

    /**
     * Get the maintenance master that owns the Maintenance
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function maintenanceMaster(): BelongsTo
    {
        return $this->belongsTo(MaintenanceMaster::class, 'maintenance_master_id', 'id');
    }

    /**
     * Get the puskesmas that owns the Maintenance
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function puskesmas(): BelongsTo
    {
        return $this->belongsTo(Puskesmas::class, 'puskesmas_id', 'id');
    }

    /**
     * Get the equipment that owns the Maintenance
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id', 'id');
    }

}
