<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $primaryKey = 'branch_id';
    public $timestamps = false; // cuma punya created_at, tanpa updated_at

    protected $fillable = ['kode_cabang', 'nama_cabang'];

    public function uploadBatches(): HasMany
    {
        return $this->hasMany(UploadBatch::class, 'branch_id', 'branch_id');
    }

    /**
     * Cari branch berdasarkan slug URL (jaktim|jakpus|jaksel).
     * Dipakai di route: Branch::bySlug($witel)
     */
    public static function bySlug(string $slug): ?self
    {
        return static::where('kode_cabang', $slug)->first();
    }
}
