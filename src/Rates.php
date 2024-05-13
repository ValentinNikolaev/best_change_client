<?php

declare(strict_types=1);

namespace BestChange;

class Rates
{
    private array $data = [];

    public function __construct(string $data)
    {
        $data = explode("\n", $data);
        foreach ($data as $row) {
            $row = iconv('CP1251', 'UTF-8', $row);
            $data = explode(';', $row);
            if (count($data) < 5) {
                continue;
            }
            $rateGive = (float) $data[3];
            $rateReceive = (float) $data[4];
            if (!$rateGive || !$rateReceive) {
                continue;
            }
            $rate = $rateGive / $rateReceive;
            $this->data[$data[0]][$data[1]][$data[2]] = [
                'exchanger_id' => (int) $data[2],
                'rate_give' => $rateGive,
                'rate_receive' => $rateReceive,
                'rate' => $rate,
                'reserve' => $data[5] ?? '',
            ];
        }
        $this->sortRateAscAll();
    }

    public function filter(int $currencyReceiveID = 0, int $currencyGiveID = 0): array
    {
        return $this->data[$currencyReceiveID][$currencyGiveID] ?? [];
    }

    public function get(): array
    {
        return $this->data;
    }

    private function sortRateAsc(array $a, array $b): int
    {
        return $a['rate'] <=> $b['rate'];
    }

    private function sortRateAscAll(): void
    {
        foreach ($this->data as $currencyReceiveID => $currencyIn) {
            foreach ($currencyIn as $currencyGiveID => $item) {
                uasort($item, [$this, 'sortRateAsc']);
                $this->data[$currencyReceiveID][$currencyGiveID] = $item;
            }
        }
    }
}