<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upload_batches', function (Blueprint $table) {
            $table->id('batch_id');

            $table->foreignId('branch_id')
                ->constrained('branches', 'branch_id')
                ->cascadeOnDelete();

            $table->date('tanggal');

            $table->string('pic')->nullable();

            $table->integer('total_data')->default(0);

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('file_hash', 64)->nullable();

            $table->timestamp('uploaded_at')->useCurrent();

            $table->index(['branch_id', 'tanggal']);

            /*
             * File yang sama tidak boleh diupload
             * dua kali pada Witel yang sama.
             *
             * Tetapi file yang sama boleh digunakan
             * pada Witel yang berbeda.
             */
            $table->unique(
                ['branch_id', 'file_hash'],
                'upload_batches_branch_file_hash_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upload_batches');
    }
};