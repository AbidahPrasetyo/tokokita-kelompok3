<?php

if (!function_exists('format_rupiah')) {
    /**
     * Format angka menjadi mata uang Rupiah tanpa desimal.
     * Contoh: 7650000 -> Rp7.650.000
     */
    function format_rupiah(int|float|string|null $angka): string
    {
        if ($angka === null || $angka === '') {
            return 'Rp0';
        }

        return 'Rp' . number_format((float) $angka, 0, ',', '.');
    }
}

if (!function_exists('format_rupiah_singkat')) {
    /**
     * Format ringkas untuk kartu ringkasan Dashboard.
     * Contoh: 24540000 -> Rp24,54 jt
     */
    function format_rupiah_singkat(int|float|string|null $angka): string
    {
        $nilai = (float) ($angka ?? 0);

        if ($nilai >= 1_000_000_000) {
            return 'Rp' . number_format($nilai / 1_000_000_000, 2, ',', '.') . ' M';
        }

        if ($nilai >= 1_000_000) {
            return 'Rp' . number_format($nilai / 1_000_000, 2, ',', '.') . ' jt';
        }

        return format_rupiah($nilai);
    }
}

if (!function_exists('format_tanggal')) {
    /**
     * Format tanggal ke gaya Indonesia.
     * $gaya = 'teks'  -> 13 Nov 2026
     * $gaya = 'slash' -> 13/11/2026
     */
    function format_tanggal(?string $tanggal, string $gaya = 'teks'): string
    {
        if (empty($tanggal)) {
            return '-';
        }

        $timestamp = strtotime($tanggal);
        if ($timestamp === false) {
            return $tanggal;
        }

        if ($gaya === 'slash') {
            return date('d/m/Y', $timestamp);
        }

        $bulanIndo = [
            1  => 'Jan',
            2  => 'Feb',
            3  => 'Mar',
            4  => 'Apr',
            5  => 'Mei',
            6  => 'Jun',
            7  => 'Jul',
            8  => 'Agu',
            9  => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des',
        ];

        $hari  = date('d', $timestamp);
        $bulan = $bulanIndo[(int) date('n', $timestamp)];
        $tahun = date('Y', $timestamp);

        return "{$hari} {$bulan} {$tahun}";
    }
}