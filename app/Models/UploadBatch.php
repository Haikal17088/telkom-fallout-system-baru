<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadBatch extends Model
{
    protected $table = 'upload_batches';

    protected $primaryKey = 'batch_id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'branch_id',
        'tanggal',
        'pic',
        'total_data',
        'uploaded_by',
        'file_hash',
    ];

    protected $casts = [
        'tanggal'    => 'date',
        'total_data' => 'integer',
    ];

    public function branch()
    {
        return $this->belongsTo(
            Branch::class,
            'branch_id',
            'branch_id'
        );
    }

    public function falloutData()
    {
        return $this->hasMany(
            FalloutData::class,
            'batch_id',
            'batch_id'
        );
    }

    public function uploader()
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by',
            'id'
        );
    }
}