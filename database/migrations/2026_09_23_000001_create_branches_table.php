<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id('branch_id');
            $table->string('kode_cabang')->unique(); // jaktim | jakpus | jaksel
            $table->string('nama_cabang');            // Witel Jakarta Timur, dst
            $table->timestamp('created_at')->useCurrent();
        });

        // ── Seed langsung 3 cabang yang sudah dipakai di sidebar ──────────
        Schema::table('branches', function (Blueprint $table) {
            //
        });

        \Illuminate\Support\Facades\DB::table('branches')->insert([
            ['kode_cabang' => 'jaktim', 'nama_cabang' => 'Witel Jakarta Timur', 'created_at' => now()],
            ['kode_cabang' => 'jakpus', 'nama_cabang' => 'Witel Jakarta Pusat', 'created_at' => now()],
            ['kode_cabang' => 'jaksel', 'nama_cabang' => 'Witel Jakarta Selatan', 'created_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
