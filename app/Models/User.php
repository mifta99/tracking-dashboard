<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'role_id',
        'puskesmas_id',
        'name',
        'email',
        'password',
        'jabatan',
        'instansi',
        'no_hp',
        'must_change_password',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'email_verified_at' => 'datetime',
        'must_change_password' => 'boolean',
    ];

    /**
     * Get the role that owns the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    /**
     * Get the puskesmas that owns the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function puskesmas(): BelongsTo
    {
        return $this->belongsTo(Puskesmas::class, 'puskesmas_id', 'id');
    }

    /**
     * Get all pengiriman records created by this user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function createdPengiriman(): HasMany
    {
        return $this->hasMany(Pengiriman::class, 'created_by', 'id');
    }

    /**
     * Get all pengiriman records updated by this user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function updatedPengiriman(): HasMany
    {
        return $this->hasMany(Pengiriman::class, 'updated_by', 'id');
    }

    /**
     * Get all uji_fungsi records created by this user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function createdUjiFungsi(): HasMany
    {
        return $this->hasMany(UjiFungsi::class, 'created_by', 'id');
    }

    /**
     * Get all uji_fungsi records updated by this user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function updatedUjiFungsi(): HasMany
    {
        return $this->hasMany(UjiFungsi::class, 'updated_by', 'id');
    }

     /**
     * Get all document records created by this user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function createdDocument(): HasMany
    {
        return $this->hasMany(Document::class, 'created_by', 'id');
    }

    /**
     * Get all document records updated by this user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function updatedDocument(): HasMany
    {
        return $this->hasMany(Document::class, 'updated_by', 'id');
    }

    /**
     * Get all of the keluhan for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reportedKeluhan(): HasMany
    {
        return $this->hasMany(Keluhan::class, 'reported_by', 'id');
    }

    /**
     * Get all of the insiden for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reportedInsiden(): HasMany
    {
        return $this->hasMany(Insiden::class, 'reported_by', 'id');
    }

    /**
     * Get all of the revisedRevisions for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function revisedRevisions(): HasMany
    {
        return $this->hasMany(Revision::class, 'revised_by', 'id');
    }

    /**
     * Get all of the resolvedRevisions for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function resolvedRevisions(): HasMany
    {
        return $this->hasMany(Revision::class, 'resolved_by', 'id');
    }

}
