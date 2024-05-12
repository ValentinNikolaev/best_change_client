<?php

declare(strict_types=1);

namespace BestChange;

class Exchangers
{
    private array $exchangerData = [];

    public function __construct(string $dataString)
    {
        $dataArray = explode("\n", $dataString);
        foreach ($dataArray as $row) {
            $row = iconv('CP1251', 'UTF-8', $row);
            $data = explode(';', $row);
            $this->exchangerData[$data[0]] = $data[1];
        }
        ksort($this->exchangerData);
    }

    public function getAllExchangers(): array
    {
        return $this->exchangerData;
    }

    public function getExchangerByID(int $id, bool $asArray = false): array
    {
        if ($asArray) {
            return empty($this->exchangerData[$id]) ? [] : [
                'id' => $id,
                'name' => $this->exchangerData[$id],
            ];
        }
        return $this->exchangerData[$id] ?? [];
    }
}