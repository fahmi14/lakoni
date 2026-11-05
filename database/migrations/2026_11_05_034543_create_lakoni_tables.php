<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tabel Wilayah
        Schema::create('wilayah', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kode')->nullable();
            $table->string('tipe'); // kecamatan, desa, sls, nks
            $table->timestamps();
        });

        // 2. Tabel Kegiatan
        Schema::create('kegiatan', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->date('mulai');
            $table->date('selesai');
            $table->json('kolom')->nullable(); // JSON list nama kolom utama
            $table->timestamps();
        });

        // 3. Tabel Konfirmasi
        Schema::create('konfirmasi', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->json('pertanyaan'); // JSON struktur pertanyaan & pilihan jawaban / jawaban terbuka
            $table->integer('jawaban')->nullable(); // default jawaban atau index
            $table->integer('sesuai')->nullable(); // index pilihan jawaban yang dianggap 'sesuai'
            $table->timestamps();
        });

        // 4. Tabel Alokasi
        Schema::create('alokasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_kegiatan')->nullable()->constrained('kegiatan')->onDelete('cascade');
            $table->string('nama_ppl');
            $table->string('nama_pml');
            $table->foreignId('id_wilayah')->nullable()->constrained('wilayah')->onDelete('set null');
            $table->timestamps();
        });

        // 5. Tabel Anomali
        Schema::create('anomali', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_kegiatan')->constrained('kegiatan')->onDelete('cascade');
            $table->foreignId('id_konfirmasi')->constrained('konfirmasi')->onDelete('cascade');
            $table->foreignId('id_alokasi')->nullable()->constrained('alokasi')->onDelete('set null');
            $table->json('kolom')->nullable(); // kolom yang disimpan
            $table->json('detail'); // data baris anomali lengkap (JSON)
            $table->json('hasil_konfirmasi')->nullable(); // jawaban dari petugas (JSON)
            $table->string('status')->default('belum'); // belum, sesuai, tidak_sesuai, dikonfirmasi
            $table->string('petugas_pengonfirmasi')->nullable();
            $table->timestamp('dikonfirmasi_pada')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anomali');
        Schema::dropIfExists('alokasi');
        Schema::dropIfExists('konfirmasi');
        Schema::dropIfExists('kegiatan');
        Schema::dropIfExists('wilayah');
    }
};
