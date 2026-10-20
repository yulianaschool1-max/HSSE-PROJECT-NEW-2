<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Support\Collection;

class ModuleManager
{
    /**
     * Dapatkan seluruh modul yang terdaftar dari konfigurasi.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return config('modules.modules', []);
    }

    /**
     * Dapatkan detail modul berdasarkan key (identifier stabil).
     *
     * @param string $key
     * @return array<string, mixed>|null
     */
    public static function get(string $key): ?array
    {
        $modules = static::all();
        return $modules[$key] ?? null;
    }

    /**
     * Dapatkan nama log standar yang digunakan untuk ActivityLog.
     *
     * @param string $key
     * @param string|null $default
     * @return string
     */
    public static function getLogName(string $key, ?string $default = null): string
    {
        $module = static::get($key);
        return $module['log_name'] ?? ($default ?? $key);
    }

    /**
     * Dapatkan daftar opsi modul untuk dropdown "Semua Modul".
     * Menggabungkan seluruh modul terdaftar dan memastikan modul dengan riwayat di DB tetap terakomodasi.
     *
     * @return array<int, array{key: string, label: string, log_name: string}>
     */
    public static function getDropdownOptions(): array
    {
        $options = [];
        $registeredLogNames = [];

        // 1. Masukkan seluruh modul terdaftar dari registrasi terpusat
        foreach (static::all() as $mod) {
            $options[] = [
                'key' => $mod['key'],
                'label' => $mod['label'],
                'log_name' => $mod['log_name'],
            ];
            $registeredLogNames[strtolower($mod['log_name'])] = true;
            foreach ($mod['aliases'] ?? [] as $alias) {
                $registeredLogNames[strtolower($alias)] = true;
            }
        }

        // 2. Periksa apakah ada modul di database yang belum terdaftar (untuk kompatibilitas penuh data lama)
        try {
            $dbModules = ActivityLog::select('module')->distinct()->pluck('module');
            foreach ($dbModules as $dbMod) {
                if ($dbMod && !isset($registeredLogNames[strtolower($dbMod)])) {
                    $options[] = [
                        'key' => strtolower(str_replace(' ', '-', $dbMod)),
                        'label' => $dbMod,
                        'log_name' => $dbMod,
                    ];
                    $registeredLogNames[strtolower($dbMod)] = true;
                }
            }
        } catch (\Throwable $e) {
            // Abaikan jika tabel belum tersedia saat instalasi awal
        }

        return $options;
    }

    /**
     * Selesaikan input filter modul dari user menjadi kumpulan nama string yang cocok di kolom `module` ActivityLog.
     *
     * @param string|null $input
     * @return array<int, string> Kosong jika tidak ada filter (Semua Modul)
     */
    public static function resolveLogNames(?string $input): array
    {
        if (!$input || in_array(strtolower($input), ['semua', 'all', ''])) {
            return [];
        }

        $inputLower = strtolower(trim($input));

        // Cari di modul terdaftar
        foreach (static::all() as $mod) {
            $matches = false;
            if (strtolower($mod['key']) === $inputLower) {
                $matches = true;
            } elseif (strtolower($mod['label']) === $inputLower) {
                $matches = true;
            } elseif (strtolower($mod['log_name']) === $inputLower) {
                $matches = true;
            } else {
                foreach ($mod['aliases'] ?? [] as $alias) {
                    if (strtolower($alias) === $inputLower) {
                        $matches = true;
                        break;
                    }
                }
            }

            if ($matches) {
                $names = array_merge([$mod['log_name']], $mod['aliases'] ?? []);
                return array_values(array_unique($names));
            }
        }

        // Jika tidak ditemukan di registrasi, cocokkan string persisnya
        return [$input];
    }

    /**
     * Format label modul agar ramah bagi pengguna di tampilan.
     *
     * @param string|null $rawModule
     * @return string
     */
    public static function formatLabel(?string $rawModule): string
    {
        if (!$rawModule) {
            return 'Sistem';
        }

        $rawLower = strtolower(trim($rawModule));

        foreach (static::all() as $mod) {
            if (strtolower($mod['key']) === $rawLower ||
                strtolower($mod['log_name']) === $rawLower) {
                return $mod['label'];
            }
            foreach ($mod['aliases'] ?? [] as $alias) {
                if (strtolower($alias) === $rawLower) {
                    return $mod['label'];
                }
            }
        }

        return $rawModule;
    }
}

