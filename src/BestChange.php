<?php

declare(strict_types=1);

namespace BestChange;

use BestChange\Exception\BestChangeException;
use DateTime;
use ZipArchive;

class BestChange
{
    private string $version = '';
    private DateTime $lastUpdate;
    private ZipArchive $zip;
    private Currencies $currencies;
    private Exchangers $exchangers;
    private Rates $rates;
    private string $cachePath;
    private bool $useCache;
    private int $cacheTime;

    private const PREFIX_TMPFILE = 'nbc';
    private const BESTCHANGE_FILE = 'http://api.bestchange.ru/info.zip';
    private const FILE_CURRENCIES = 'bm_cy.dat';
    private const FILE_CURRENCIES_CODES = 'bm_cycodes.dat';
    private const FILE_EXCHANGERS = 'bm_exch.dat';
    private const FILE_RATES = 'bm_rates.dat';
    private const TIMEOUT = 15;

    /**
     * @throws \BestChange\Exception\BestChangeException
     */
    public function __construct(string $cachePath = '', int $cacheTime = 3600)
    {
        $this->zip = new ZipArchive();
        if ($cachePath) {
            $this->cacheTime = $cacheTime;
            $this->useCache = true;
            $this->cachePath = $cachePath;
        } else {
            $this->useCache = false;
            $this->cachePath = tempnam(sys_get_temp_dir(), self::PREFIX_TMPFILE);
        }
        register_shutdown_function([$this, 'cleanUp']); // clean up
        $this->loadData();
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getRatesInstance(): Rates
    {
        return $this->rates;
    }

    public function getLastUpdate(): DateTime
    {
        return $this->lastUpdate;
    }

    public function getCurrencies(): array
    {
        return $this->currencies->getAllCurrencies();
    }

    public function getExchangers(): array
    {
        return $this->exchangers->getAllExchangers();
    }

    public function cleanUp(): self
    {
        if (!$this->useCache) {
            if (!is_writable($this->cachePath)) {
                chmod($this->cachePath, 0644);
            }
            unlink($this->cachePath);
        }
        return $this;
    }

    /**
     * @throws \BestChange\Exception\BestChangeException
     * @throws \Exception
     */
    private function loadData(): self
    {
        $this->retrieveFile()->unzipFile()->initializeData();
        $this->currencies = new Currencies(
            $this->zip->getFromName(self::FILE_CURRENCIES),
            $this->zip->getFromName(self::FILE_CURRENCIES_CODES)
        );
        $this->exchangers = new Exchangers($this->zip->getFromName(self::FILE_EXCHANGERS));
        $this->rates = new Rates($this->zip->getFromName(self::FILE_RATES));
        return $this;
    }

    /**
     * @throws \BestChange\Exception\BestChangeException
     */
    private function retrieveFile(): self
    {
        if ($this->canUseCacheFile()) {
            return $this;
        }
        $file = $this->downloadFile();
        if ($file) {
            $fp = fopen($this->cachePath, 'wb+');
            fputs($fp, $file);
            fclose($fp);
            return $this;
        }
        throw new BestChangeException('File "' . self::BESTCHANGE_FILE . '" on bestchange.ru not found or inaccessible');
    }

    private function canUseCacheFile(): bool
    {
        clearstatcache(true, $this->cachePath);
        return (
            $this->useCache
            && file_exists($this->cachePath)
            && filemtime($this->cachePath) > (time() - $this->cacheTime)
        );
    }

    /**
     * @throws \BestChange\Exception\BestChangeException
     */
    private function unzipFile(): self
    {
        if (!$this->zip->open($this->cachePath)) {
            throw new BestChangeException('Received a corrupted file from bestchange.ru');
        }
        return $this;
    }

    /**
     * @throws \Exception
     */
    private function initializeData(): void
    {
        $file = explode("\n", $this->zip->getFromName('bm_info.dat'));
        foreach ($file as $row) {
            $row = iconv('CP1251', 'UTF-8', $row);
            $data = array_map('trim', explode('=', $row));
            if (count($data) < 2) {
                continue;
            }
            switch ($data[0]) {
                case'last_update':
                    $this->lastUpdate = $this->convertToCanonicalDate($data[1]);
                    break;
                case'current_version':
                    $this->version = $data[1];
                    break;
            }
        }
    }

    /**
     * @throws \Exception
     */
    private function convertToCanonicalDate(string $date): DateTime
    {
        $arMonth = [
            'января' => 'January',
            'февраля' => 'February',
            'марта' => 'March',
            'апреля' => 'April',
            'мая' => 'May',
            'июня' => 'June',
            'июля' => 'July',
            'августа' => 'August',
            'сентября' => 'September',
            'октября' => 'October',
            'ноября' => 'November',
            'декабря' => 'December',
        ];
        foreach ($arMonth as $ru => $en) {
            $date = preg_replace('/' . $ru . '/sui', $en, $date);
        }
        return new DateTime($date);
    }

    private function downloadFile(): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::BESTCHANGE_FILE);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}