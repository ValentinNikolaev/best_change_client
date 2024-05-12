<?php

declare(strict_types=1);

namespace BestChange;

class Currencies
{
    private array $currencyData = [];
    private array $currencyCodes = [];

    public function __construct(string $currencyDataString, string $codesString)
    {
        $currencyDataArray = explode("\n", $currencyDataString);
        $codesArray = explode("\n", $codesString);

        foreach ($currencyDataArray as $row) {
            $row = iconv('CP1251', 'UTF-8', $row);
            $currencyData = explode(';', $row);
            $this->currencyData[$currencyData[0]] = [
                'id' => (int)$currencyData[0],
                'name' => $currencyData[2],
            ];
        }

        foreach ($codesArray as $code) {
            $row = iconv('CP1251', 'UTF-8', $code);
            $currencyCode = explode(';', $row);
            $this->currencyCodes[$currencyCode[0]] = $currencyCode[1];
        }
        uasort($this->currencyData, function (array $a, array $b): int {
            return strcasecmp($a['name'], $b['name']);
        });
        foreach ($this->currencyData as $id => $item) {
            $this->currencyData[$id]['code'] = $this->currencyCodes[$id] ?? null;
        }
    }

    public function getAllCurrencies(): array
    {
        return $this->currencyData;
    }

    public function getCurrencyByID(int $id): array
    {
        return $this->currencyData[$id] ?? [];
    }
}