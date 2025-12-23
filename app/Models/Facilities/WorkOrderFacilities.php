<?php

namespace App\Models\Facilities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\FacilityTech; // Pastikan Model Teknisi di-import
use App\Models\Engineering\Machine;

class WorkOrderFacilities extends Model
{
    use HasFactory;

    protected $table = 'work_order_facilities';
    protected $guarded = ['id'];

    // Agar NIK & Divisi muncul di JSON Modal (AlpineJS)
    protected $appends = ['nik_pelapor', 'divisi_pelapor', 'requester_name'];

    // ==========================================
    // 1. RELASI USER (Hybrid: Login & Guest)
    // ==========================================
    public function user()
    {
        return $this->belongsTo(User::class, 'requester_id')
            ->withDefault([
                'id' => null,
                'name' => null,
                'nik' => null,
                'division' => null
            ]);
    }

    // ==========================================
    // 2. ACCESSORS (Logika Tampilan Data)
    // ==========================================

    // Logika Nama: Cek User Login -> Cek Kolom Manual -> Default
    public function getRequesterNameAttribute($value)
    {
        // 1. Jika User Login
        if ($this->user && $this->user->id) {
            return $this->user->name;
        }
        // 2. Jika ada data di kolom requester_name (Guest/User Resign)
        if (!empty($value)) {
            return $value;
        }
        if (!empty($this->attributes['requester_name'])) {
            return $this->attributes['requester_name'];
        }
        return 'Guest / Tanpa Nama';
    }

    // Logika NIK
    public function getNikPelaporAttribute()
    {
        if ($this->user && $this->user->id) {
            return $this->user->nik;
        }
        // Ambil dari kolom fisik 'requester_nik'
        return $this->attributes['requester_nik'] ?? '-';
    }

    // Logika Divisi
    public function getDivisiPelaporAttribute()
    {
        if ($this->user && $this->user->id) {
            return $this->user->division ?? $this->user->divisi;
        }
        // Ambil dari kolom fisik 'requester_division'
        return $this->attributes['requester_division'] ?? '-';
    }

    // ==========================================
    // 3. RELASI LAINNYA (INI YANG TADI ERROR)
    // ==========================================

    // Relasi ke Teknisi (Many-to-Many)
    public function technicians()
    {
        return $this->belongsToMany(
            \App\Models\FacilityTech::class,
            'facility_tech_work_order', // Nama tabel pivot
            'work_order_facility_id',   // FK model ini
            'facility_tech_id'          // FK model target
        );
    }

    // Relasi ke Mesin
    public function machine()
    {
        return $this->belongsTo(Machine::class, 'machine_id');
    }
}
