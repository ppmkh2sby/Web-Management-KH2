<?php

namespace App\Services;

use App\Models\Presensi;
use App\Models\Santri;
use App\Models\Sesi;

class PresensiRekapService
{
    /**
     * Rekap kehadiran santri dalam 1 bulan.
     * total_sesi dihitung dari Sesi yang kelas-nya mencakup kelas santri.
     */
    public function rekapBulanan(Santri $santri, int $bulan, int $tahun): array
    {
        $baseQuery = Sesi::query()
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan);

        if ($santri->kelas_id) {
            $baseQuery->whereHas('kelas', fn ($q) => $q->where('kelas.id', $santri->kelas_id));
        }

        $totalSesi = (clone $baseQuery)->count();

        $presensiBulan = Presensi::query()
            ->where('santri_id', $santri->id)
            ->whereHas('sesi', fn ($q) => $q
                ->whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $bulan)
            );

        $hadir = (clone $presensiBulan)->where('status', 'hadir')->count();
        $izin = (clone $presensiBulan)->where('status', 'izin')->count();
        $sakit = (clone $presensiBulan)->where('status', 'sakit')->count();
        $alpha = (clone $presensiBulan)->where('status', 'alpha')->count();

        return [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'total_sesi' => $totalSesi,
            'hadir' => $hadir,
            'izin' => $izin,
            'sakit' => $sakit,
            'alpha' => $alpha,
            'persentase' => $totalSesi > 0 ? round($hadir / $totalSesi * 100) : 0,
        ];
    }

    /**
     * Rekap per kegiatan spesifik (misal: sambung subuh bulan ini).
     */
    public function rekapPerKegiatan(Santri $santri, string $kategori, string $waktu, int $bulan, int $tahun): array
    {
        $baseQuery = Sesi::query()
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->whereHas('kegiatan', fn ($q) => $q->where('kategori', $kategori)->where('waktu', $waktu));

        if ($santri->kelas_id) {
            $baseQuery->whereHas('kelas', fn ($q) => $q->where('kelas.id', $santri->kelas_id));
        }

        $totalSesi = (clone $baseQuery)->count();

        $hadir = Presensi::query()
            ->where('santri_id', $santri->id)
            ->where('status', 'hadir')
            ->whereHas('sesi', fn ($q) => $q
                ->whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $bulan)
                ->whereHas('kegiatan', fn ($kq) => $kq->where('kategori', $kategori)->where('waktu', $waktu))
            )
            ->count();

        return [
            'kegiatan' => sprintf('%s (%s)', $kategori, $waktu),
            'total_sesi' => $totalSesi,
            'hadir' => $hadir,
            'persentase' => $totalSesi > 0 ? round($hadir / $totalSesi * 100) : 0,
        ];
    }
}
