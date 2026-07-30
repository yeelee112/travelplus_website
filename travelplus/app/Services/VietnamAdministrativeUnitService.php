<?php

namespace App\Services;

use RuntimeException;

class VietnamAdministrativeUnitService
{
    private const DATA_FILE = 'assets/data/vietnam-administrative-units-2025.json';

    /** @var array<string, mixed>|null */
    private static ?array $cachedDataset = null;

    /**
     * @return list<array{code: string, name: string}>
     */
    public function provinces(): array
    {
        $provinces = [];

        foreach ($this->dataset()['provinces'] as $province) {
            $provinces[] = [
                'code' => (string) ($province['code'] ?? ''),
                'name' => (string) ($province['name'] ?? ''),
            ];
        }

        return $provinces;
    }

    /**
     * @return array{province_code: string, province_name: string, ward_code: string, ward_name: string}|null
     */
    public function resolve(string $provinceCode, string $wardCode): ?array
    {
        $provinceCode = $this->normalizeCode($provinceCode, 2);
        $wardCode = $this->normalizeCode($wardCode, 5);

        if ($provinceCode === '' || $wardCode === '') {
            return null;
        }

        foreach ($this->dataset()['provinces'] as $province) {
            if ((string) ($province['code'] ?? '') !== $provinceCode) {
                continue;
            }

            foreach (($province['wards'] ?? []) as $ward) {
                if ((string) ($ward['code'] ?? '') !== $wardCode) {
                    continue;
                }

                return [
                    'province_code' => $provinceCode,
                    'province_name' => (string) ($province['name'] ?? ''),
                    'ward_code' => $wardCode,
                    'ward_name' => (string) ($ward['name'] ?? ''),
                ];
            }

            return null;
        }

        return null;
    }

    /**
     * @param array{province_name: string, ward_name: string} $unit
     */
    public function formatAddress(string $addressLine, array $unit): string
    {
        return implode(', ', array_filter([
            trim($addressLine, " \t\n\r\0\x0B,"),
            trim($unit['ward_name']),
            trim($unit['province_name']),
        ], static fn(string $part): bool => $part !== ''));
    }

    public function dataUrl(): string
    {
        return base_url(self::DATA_FILE);
    }

    /**
     * @return array<string, mixed>
     */
    private function dataset(): array
    {
        if (self::$cachedDataset !== null) {
            return self::$cachedDataset;
        }

        $path = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, self::DATA_FILE);
        $json = is_file($path) ? file_get_contents($path) : false;

        if (! is_string($json) || $json === '') {
            throw new RuntimeException('Vietnam administrative dataset is unavailable.');
        }

        $dataset = json_decode($json, true);
        if (! is_array($dataset)
            || (int) ($dataset['province_count'] ?? 0) !== 34
            || (int) ($dataset['ward_count'] ?? 0) !== 3321
            || ! is_array($dataset['provinces'] ?? null)) {
            throw new RuntimeException('Vietnam administrative dataset is invalid.');
        }

        self::$cachedDataset = $dataset;

        return self::$cachedDataset;
    }

    private function normalizeCode(string $code, int $length): string
    {
        $code = trim($code);

        if ($code === '' || ! ctype_digit($code) || strlen($code) > $length) {
            return '';
        }

        return str_pad($code, $length, '0', STR_PAD_LEFT);
    }
}
