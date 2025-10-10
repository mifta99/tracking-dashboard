<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Maintenance;

class MaintenanceMaster extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $table = 'maintenance_master';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'kuartal',
        'start_period',
        'end_period',
        'description',
        'layanan',
        'waktu_pengecekan',
        'kunjungan',
        'total_active_days',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'kuartal' => 'integer',
        'start_period' => 'date',
        'end_period' => 'date',
        'total_active_days' => 'integer',
    ];

    /**
     * Get all of the maintenance for the MaintenanceMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function maintenance(): HasMany
    {
        return $this->hasMany(Maintenance::class, 'maintenance_master_id', 'id');
    }
}
