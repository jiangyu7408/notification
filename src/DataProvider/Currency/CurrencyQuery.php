<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/19
 * Time: 18:00.
 */
namespace DataProvider\Currency;

/**
 * Class CurrencyQuery.
 */
class CurrencyQuery
{
    /** @var float[] */
    protected static $cache = [];

    /**
     * @param string $fromCurrency
     * @param string $toCurrency
     *
     * @return float
     */
    public static function query($fromCurrency, $toCurrency = 'USD')
    {
        $fromCurrency = strtoupper($fromCurrency);
        $toCurrency = strtoupper($toCurrency);
        if ($fromCurrency === 'FBC' || $fromCurrency === 'USD') {
            return 1.0;
        }

        $query = sprintf('%s/%s', $toCurrency, $fromCurrency);
        if (array_key_exists($query, self::$cache)) {
            return self::$cache[$query];
        }

        static $rates;
        if ($rates === null) {
            $rates = self::makeContentProvider()->getRates();
        }

        if (!array_key_exists($query, $rates)) {
            throw new \InvalidArgumentException('not supported: '.$query);
        }

        $rate = (float) sprintf('%.4f', (1 / $rates[$query]));
        self::$cache[$query] = $rate;

        return $rate;
    }

    /**
     * @return YahooCurrency
     */
    protected static function makeContentProvider()
    {
        static $instance;

        if ($instance === null) {
            $instance = new YahooCurrency(CONFIG_DIR.'/log/.currency.cache');
        }

        return $instance;
    }
}
