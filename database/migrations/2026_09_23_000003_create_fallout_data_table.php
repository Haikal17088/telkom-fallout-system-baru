<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fallout_data', function (Blueprint $table) {
            $table->id('row_id');
            $table->foreignId('batch_id')->constrained('upload_batches', 'batch_id')->cascadeOnDelete();

            $table->string('order_id');            // No/Order ID asli dari file upload (kolom "id" di ERD)
            $table->text('deskripsi')->nullable();
            $table->string('sto', 20)->nullable();
            $table->date('tanggal');
            $table->string('pic')->nullable();
            $table->string('resolved_eskalasi')->nullable(); // RESOLVED | ESKALASI
            $table->string('status')->nullable();             // Process OSS (...) | COMPLETED | dst
            $table->string('ket')->nullable();

            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at')->useCurrent();

            // index yang paling sering dipakai buat filter di halaman Total Rekap / Detail Fallout
            $table->index(['batch_id', 'tanggal']);
            $table->index('sto');
            $table->index('resolved_eskalasi');
            $table->unique(['batch_id', 'order_id']); // cegah duplikat order_id dalam 1 batch upload
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fallout_data');
    }
};
