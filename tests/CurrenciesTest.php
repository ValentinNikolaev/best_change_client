<?php

namespace Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use BestChange\Currencies;

class CurrenciesTest extends TestCase
{
    private string $filepath = __DIR__ . '/Fixtures/info/bm_cy.dat';
    private Currencies $currencies;

    /**
     * CurrenciesTest constructor.
     *
     * @param null $name
     * @param array $data
     * @param  string  $dataName
     * @todo fix test
     *
     * @throws Exception
     */
    public function __construct($name = null, array $data = [], string $dataName = '')
    {
        $this->currencies = new Currencies(file_get_contents($this->filepath));
        parent::__construct($name, $data, $dataName);
    }

    /**
     * Загружаем данные. Всего должно быть 146 видов валют
     */
    public function testLoad()
    {
        $dataCurrency = $this->currencies->getAllCurrencies();
        $this->assertEquals(count($dataCurrency), 146);
    }

    /**
     * метод getByID
     */
    public function testGetById()
    {
        $currency = $this->currencies->getCurrencyByID(107);
        $this->assertEquals($currency['name'], 'Золотая Корона RUB');
        $this->assertEquals($currency['id'], 107);
        $this->assertEquals($currency['code'], 'GCMTRUB');

        $currency = $this->currencies->getCurrencyByID(124);
        $this->assertEquals($currency['id'], 124);
        $this->assertEquals($currency['name'], 'Payza EUR');
        $this->assertEquals($currency['code'], 'PAEUR');
    }
}