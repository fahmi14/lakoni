@extends('layouts.app')

@section('title', 'Infografis Panduan Penggunaan — LAKONI')

@push('styles')
<style>
    /* Keyframes Motion & Animasi Pastel */
    @keyframes pulseGlow {
        0%, 100% {
            box-shadow: 0 0 0 0 rgba(79, 111, 246, 0.4);
        }
        50% {
            box-shadow: 0 0 0 10px rgba(79, 111, 246, 0);
        }
    }

    @keyframes floatSoft {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-6px); }
    }

    @keyframes lineFlow {
        0% { background-position: 0 0; }
        100% { background-position: 0 1000px; }
    }

    .infografis-container {
        max-width: 980px;
        margin: 0 auto;
    }

    /* Motion Hero Header */
    .hero-badge-motion {
        animation: floatSoft 3.5s ease-in-out infinite;
        display: inline-block;
    }

    /* Garis Waktu dengan Animasi Flow */
    .timeline-vertical {
        position: relative;
        padding: 1.5rem 0;
    }

    .timeline-vertical::before {
        content: '';
        position: absolute;
        top: 2rem;
        bottom: 2rem;
        left: 32px;
        width: 4px;
        background: linear-gradient(180deg, #4f6ff6, #8b5cf6, #10b981, #4f6ff6);
        background-size: 100% 400px;
        animation: lineFlow 12s linear infinite;
        border-radius: 4px;
        opacity: 0.35;
    }

    @media (min-width: 768px) {
        .timeline-vertical::before {
            left: 50%;
            transform: translateX(-50%);
        }
    }

    .timeline-step {
        position: relative;
        margin-bottom: 2.8rem;
        display: flex;
        flex-direction: column;
        opacity: 0;
        transform: translateY(24px);
        transition: opacity 0.6s cubic-bezier(0.16, 1, 0.3, 1), transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .timeline-step.visible {
        opacity: 1;
        transform: translateY(0);
    }

    @media (min-width: 768px) {
        .timeline-step {
            flex-direction: row;
            align-items: center;
        }
        .timeline-step:nth-child(even) {
            flex-direction: row-reverse;
        }
    }

    /* Step Circle Badge dengan Motion Pulse */
    .timeline-step-badge {
        position: absolute;
        left: 13px;
        top: 0;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: #ffffff;
        border: 4px solid var(--lk-primary);
        color: var(--lk-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-family: 'Outfit', sans-serif;
        font-size: 1.05rem;
        box-shadow: 0 4px 14px rgba(79, 111, 246, 0.25);
        z-index: 2;
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .timeline-step:hover .timeline-step-badge {
        transform: scale(1.18);
        animation: pulseGlow 1.8s infinite;
    }

    @media (min-width: 768px) {
        .timeline-step-badge {
            left: 50%;
            transform: translateX(-50%);
        }
        .timeline-step:hover .timeline-step-badge {
            transform: translateX(-50%) scale(1.18);
        }
    }

    /* Step Card dengan Hover Motion Kalem */
    .timeline-step-card {
        background: #ffffff;
        border-radius: var(--lk-radius);
        border: 1px solid var(--lk-border-card);
        padding: 1.6rem;
        box-shadow: var(--lk-shadow-sm);
        margin-left: 70px;
        position: relative;
        transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    @media (min-width: 768px) {
        .timeline-step-card {
            width: 44%;
            margin-left: 0;
        }
        .timeline-step:nth-child(odd) .timeline-step-card {
            margin-right: auto;
        }
        .timeline-step:nth-child(even) .timeline-step-card {
            margin-left: auto;
        }
    }

    .timeline-step-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 16px 36px -6px rgba(79, 111, 246, 0.12);
        border-color: rgba(79, 111, 246, 0.3);
    }

    /* Pill Tag Hover Effect */
    .step-pill-tag {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 0.76rem;
        font-weight: 600;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        color: #475569;
        transition: all 0.2s ease;
    }

    .step-pill-tag:hover {
        background: var(--lk-primary-soft);
        color: var(--lk-primary);
        border-color: var(--lk-primary-soft);
        transform: scale(1.04);
    }
</style>
@endpush

@section('content')
<div class="infografis-container">
    <!-- Header Infografis dengan Motion -->
    <div class="text-center mb-5">
        <div class="hero-badge-motion">
            <span class="badge-pastel-primary mb-2 px-3 py-2 fw-bold shadow-xs">
                <i class="fa-solid fa-compass me-1"></i> PANDUAN PENGGUNAAN APLIKASI
            </span>
        </div>
        <h2 class="display-6 fw-bold brand-font text-dark mb-2">Infografis Alur Kerja LAKONI</h2>
        <p class="text-muted fs-6 mb-0 max-w-600 mx-auto">
            Panduan visual langkah demi langkah pengoperasian sistem dari inisialisasi master kegiatan hingga ekspor respon anomali.
        </p>
    </div>

    <!-- Timeline Steps dengan Scroll Motion -->
    <div class="timeline-vertical">

        <!-- STEP 1 -->
        <div class="timeline-step">
            <div class="timeline-step-badge" style="border-color: #94a3b8; color: #475569;">1</div>
            <div class="timeline-step-card">
                <span class="badge-pastel-neutral mb-2"><i class="fa-solid fa-map-location-dot me-1"></i> Tahap Persiapan</span>
                <h4 class="fw-bold text-dark fs-5 mb-2">Master Wilayah</h4>
                <p class="text-muted small mb-3">Memastikan data unit wilayah survei (Kecamatan, Desa, SLS, atau NKS) telah terdaftar di database.</p>
                <div class="d-flex flex-wrap gap-1">
                    <span class="step-pill-tag"><i class="fa-solid fa-hashtag text-secondary"></i> ID Wilayah</span>
                    <span class="step-pill-tag"><i class="fa-solid fa-layer-group text-secondary"></i> Tipe Wilayah</span>
                    <span class="step-pill-tag" style="background: var(--lk-primary-soft); color: var(--lk-primary);"><i class="fa-solid fa-shield-halved"></i> Akses Admin</span>
                </div>
            </div>
        </div>

        <!-- STEP 2 -->
        <div class="timeline-step">
            <div class="timeline-step-badge" style="border-color: #4f6ff6; color: #4f6ff6;">2</div>
            <div class="timeline-step-card">
                <span class="badge-pastel-primary mb-2"><i class="fa-solid fa-calendar-check me-1"></i> Inisialisasi</span>
                <h4 class="fw-bold text-dark fs-5 mb-2">Tambah Kegiatan Survei</h4>
                <p class="text-muted small mb-3">Mendaftarkan nama survei/sensus, menentukan masa aktif, dan draf template WhatsApp otomatis.</p>
                <div class="d-flex flex-wrap gap-1">
                    <span class="step-pill-tag" style="background: var(--lk-primary-soft); color: var(--lk-primary);"><i class="fa-solid fa-key"></i> Kode Unik (misal: SBR2026)</span>
                    <span class="step-pill-tag"><i class="fa-solid fa-clock text-secondary"></i> Jadwal Aktif</span>
                    <span class="step-pill-tag" style="background: rgba(16, 185, 129, 0.1); color: #10b981;"><i class="fa-brands fa-whatsapp"></i> Template WA</span>
                </div>
            </div>
        </div>

        <!-- STEP 3 -->
        <div class="timeline-step">
            <div class="timeline-step-badge" style="border-color: #8b5cf6; color: #8b5cf6;">3</div>
            <div class="timeline-step-card">
                <span class="badge-pastel-purple mb-2"><i class="fa-solid fa-circle-question me-1"></i> Formulir Dinamis</span>
                <h4 class="fw-bold text-dark fs-5 mb-2">Template Konfirmasi (JSON)</h4>
                <p class="text-muted small mb-3">Menyusun pertanyaan verifikasi konfirmasi lapangan dalam bentuk pilihan ganda atau isian uraian bebas.</p>
                <div class="d-flex flex-wrap gap-1">
                    <span class="step-pill-tag"><i class="fa-solid fa-list-check text-secondary"></i> Pilihan Ganda</span>
                    <span class="step-pill-tag"><i class="fa-solid fa-align-left text-secondary"></i> Uraian Terbuka</span>
                    <span class="step-pill-tag" style="background: rgba(16, 185, 129, 0.1); color: #10b981;"><i class="fa-solid fa-check-double"></i> Kriteria Valid</span>
                </div>
            </div>
        </div>

        <!-- STEP 4 -->
        <div class="timeline-step">
            <div class="timeline-step-badge" style="border-color: #f59e0b; color: #f59e0b;">4</div>
            <div class="timeline-step-card">
                <span class="badge-pastel-warning mb-2"><i class="fa-solid fa-user-group me-1"></i> Pemetaan Petugas</span>
                <h4 class="fw-bold text-dark fs-5 mb-2">Alokasi Petugas (Manual / Excel)</h4>
                <p class="text-muted small mb-3">Memetakan nama Petugas Pencacah (PPL), Pengawas (PML), dan wilayah tugasnya melalui upload file Excel atau manual.</p>
                <div class="d-flex flex-wrap gap-1">
                    <span class="step-pill-tag" style="background: rgba(16, 185, 129, 0.1); color: #10b981;"><i class="fa-solid fa-file-excel"></i> Template Excel</span>
                    <span class="step-pill-tag"><i class="fa-solid fa-user text-primary"></i> Petugas PPL</span>
                    <span class="step-pill-tag"><i class="fa-solid fa-user-tie text-info"></i> Pengawas PML</span>
                </div>
            </div>
        </div>

        <!-- STEP 5 -->
        <div class="timeline-step">
            <div class="timeline-step-badge" style="border-color: #3b82f6; color: #3b82f6;">5</div>
            <div class="timeline-step-card">
                <span class="badge-pastel-primary mb-2"><i class="fa-solid fa-file-import me-1"></i> Upload Anomali</span>
                <h4 class="fw-bold text-dark fs-5 mb-2">Import Data Anomali (Multi-File)</h4>
                <p class="text-muted small mb-3">Mengunggah file anomali (.xlsx / .csv), memetakan kolom wilayah penghubung agar otomatis tersambung ke petugas.</p>
                <div class="d-flex flex-wrap gap-1">
                    <span class="step-pill-tag"><i class="fa-solid fa-copy text-secondary"></i> Multi-File Upload</span>
                    <span class="step-pill-tag" style="background: rgba(245, 158, 11, 0.1); color: #d97706;"><i class="fa-solid fa-link"></i> Penghubung Wilayah</span>
                    <span class="step-pill-tag" style="background: rgba(16, 185, 129, 0.1); color: #10b981;"><i class="fa-solid fa-shield-halved"></i> CSV Quotes Sanitizer</span>
                </div>
            </div>
        </div>

        <!-- STEP 6 -->
        <div class="timeline-step">
            <div class="timeline-step-badge" style="border-color: #10b981; color: #10b981;">6</div>
            <div class="timeline-step-card">
                <span class="badge-pastel-success mb-2"><i class="fa-solid fa-mobile-screen-button me-1"></i> Akses Petugas</span>
                <h4 class="fw-bold text-dark fs-5 mb-2">Konfirmasi Lapangan (Tanpa Login)</h4>
                <p class="text-muted small mb-3">Petugas memasukkan Kode Kegiatan, memilih nama, mengatur kolom detail, lalu mengisi jawaban konfirmasi.</p>
                <div class="d-flex flex-wrap gap-1">
                    <span class="step-pill-tag" style="background: rgba(16, 185, 129, 0.1); color: #10b981;"><i class="fa-solid fa-unlock"></i> Tanpa Password</span>
                    <span class="step-pill-tag"><i class="fa-solid fa-table-columns text-primary"></i> Atur Kolom Detail</span>
                    <span class="step-pill-tag"><i class="fa-solid fa-magnifying-glass text-secondary"></i> Custom Search</span>
                </div>
            </div>
        </div>

        <!-- STEP 7 -->
        <div class="timeline-step">
            <div class="timeline-step-badge" style="border-color: #059669; color: #059669;">7</div>
            <div class="timeline-step-card">
                <span class="badge-pastel-success mb-2"><i class="fa-solid fa-chart-line me-1"></i> Rekapitulasi</span>
                <h4 class="fw-bold text-dark fs-5 mb-2">Monitoring & Ekspor Respon</h4>
                <p class="text-muted small mb-3">Memantau progres komparasi belum konfirmasi per petugas dan mengunduh seluruh respon Excel dengan kolom terurai.</p>
                <div class="d-flex flex-wrap gap-1">
                    <span class="step-pill-tag" style="background: var(--lk-primary-soft); color: var(--lk-primary);"><i class="fa-solid fa-table"></i> Tabel Rekapitulasi</span>
                    <span class="step-pill-tag" style="background: rgba(16, 185, 129, 0.1); color: #10b981;"><i class="fa-brands fa-whatsapp"></i> Chat WA Petugas</span>
                    <span class="step-pill-tag"><i class="fa-solid fa-file-excel text-success"></i> Download Respon</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Card Info Bawah dengan Motion CTA -->
    <div class="card card-clean p-4 mt-2 text-center" style="background: linear-gradient(135deg, #eff3ff 0%, #f5f3ff 100%);">
        <h4 class="fw-bold text-dark mb-1">Siap Menggunakan LAKONI?</h4>
        <p class="text-muted small mb-3">Seluruh modul saling terintegrasi otomatis untuk mempercepat proses konfirmasi data anomali lapangan.</p>
        <div class="d-flex justify-content-center gap-2">
            <a href="{{ route('kegiatan.create') }}" class="btn btn-pastel-primary">
                <i class="fa-solid fa-plus me-1"></i> Buat Kegiatan Baru
            </a>
            <a href="{{ route('home') }}" target="_blank" class="btn btn-pastel-soft">
                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Area Petugas
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Intersection Observer untuk memicu animasi motion saat discroll
        const stepItems = document.querySelectorAll('.timeline-step');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, index) => {
                if (entry.isIntersecting) {
                    setTimeout(() => {
                        entry.target.classList.add('visible');
                    }, index * 80);
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.15,
            rootMargin: '0px 0px -50px 0px'
        });

        stepItems.forEach(item => {
            observer.observe(item);
        });
    });
</script>
@endpush
