<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fallouts', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->unique();          // No/Order ID
            $table->text('deskripsi')->nullable();          // Deskripsi (Provisioning Failed...)
            $table->string('sto', 20);                      // STO: CPE, TBE, JAG, KBY, BIN, dll
            $table->string('witel', 50);                     // Witel Jakarta Timur/Pusat/Selatan
            $table->date('tanggal_fallout');                 // Tgl Fallout
            $table->string('pic', 100)->nullable();          // PIC penanggung jawab
            $table->enum('resolusi', ['RESOLVED', 'ESKALASI']); // status Resolved/Eskalasi
            $table->string('status_proses')->nullable();     // "Process OSS (...)" dsb
            $table->string('keterangan')->nullable();        // KET (angka/catatan tambahan)
            $table->boolean('is_baru')->default(false);      // badge "BARU" saat upload
            $table->timestamps();

            $table->index(['tanggal_fallout', 'witel']);
            $table->index('resolusi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fallouts');
    }
};
