<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/19
 * Time: 17:26.
 */
namespace DataProvider\Currency;

/**
 * Class YahooCurrency.
 */
class YahooCurrency
{
    /** @var string */
    protected $cacheFile;

    /**
     * YahooCurrency constructor.
     *
     * @param string $cacheFile
     */
    public function __construct($cacheFile)
    {
        $this->cacheFile = $cacheFile;
    }

    /**
     * @return array
     * @throws \QueryPath\Exception
     */
    public function getRates()
    {
        $data = $this->fetch();
        $rates = (new YahooCurrencyQuotaParser())->parse($data);

        return $rates;
    }

    /**
     * @return string
     */
    protected function fetch()
    {
        $cacheFile = $this->cacheFile;
        if (file_exists($cacheFile)) {
            $localCache = file_get_contents($cacheFile);
            if ($localCache) {
                return $localCache;
            }
        }

        $content = $this->realFetch();
        file_put_contents($cacheFile, $content);

        return $content;
    }

    /**
     * @return string
     */
    protected function realFetch()
    {
        $url = 'http://finance.yahoo.com/webservice/v1/symbols/allcurrencies/quote';

        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        $ret = curl_exec($curlHandle);
        curl_close($curlHandle);

        return $ret;
    }
}
