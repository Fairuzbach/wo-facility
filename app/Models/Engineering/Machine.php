<?php

namespace App\Models\Engineering;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Machine extends Model
{
    use HasFactory;

    protected $table = 'machines';

    // Kolom yang bisa diisi mass assignment
    protected $fillable = [
        'name',
        'plant_id',
        'name',
        'sub_plant',  // Untuk PE dan MT (A-F)
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke Plant
     */
    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class, 'plant_id');
    }

    /**
     * Scope untuk filter by plant
     */
    public function scopeByPlant($query, $plantId)
    {
        return $query->where('plant_id', $plantId);
    }

    /**
     * Scope untuk filter by sub plant
     */
    public function scopeBySubPlant($query, $subPlant)
    {
        return $query->where('sub_plant', $subPlant);
    }

    /**
     * Accessor untuk full name (dengan sub plant jika ada)
     */
    public function getFullNameAttribute()
    {
        if ($this->sub_plant) {
            return $this->name . ' (Plant ' . $this->sub_plant . ')';
        }
        return $this->name;
    }
}
