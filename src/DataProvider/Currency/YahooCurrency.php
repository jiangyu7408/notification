<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/04/19
 * Time: 17:26.
 */
namespace DataProvider\Currency;

use DOMElement;

/**
 * Class YahooCurrency.
 */
class YahooCurrency
{
    /** @var array */
    protected $currencyList;
    /** @var string */
    protected $cacheFile;

    /**
     * YahooCurrency constructor.
     *
     * @param array  $currencyList
     * @param string $cacheFile
     */
    public function __construct(array $currencyList, $cacheFile)
    {
        $this->currencyList = $currencyList;
        $this->cacheFile = $cacheFile;
    }

    /**
     * @return array
     * @throws \QueryPath\Exception
     */
    public function getRates()
    {
        $data = $this->fetch();
        $rates = [];
        $queryPath = qp($data, 'query>results>rate');
        $queryPath->each(function ($index, DOMElement $element) use (&$rates) {
            assert(is_int($index));
            $name = null;
            /** @var DOMElement $node */
            foreach ($element->childNodes as $node) {
                if ($node->nodeName === 'Name') {
                    $name = $node->textContent;
                }
                if ($node->nodeName === 'Rate') {
                    $rates[$name] = $node->textContent;
                }
            }
        });

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
        $pairs = array_map(
            function ($currency) {
                return $currency.'USD';
            },
            $this->currencyList
        );

        $data = [
            'q' => sprintf('select * from yahoo.finance.xchange where pair in ("%s")', implode('","', $pairs)),
            'env' => 'store://datatables.org/alltableswithkeys',
        ];
        $curlPost = http_build_query($data);

        $url = 'http://query.yahooapis.com/v1/public/yql';

        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL, $url);
        curl_setopt($curlHandle, CURLOPT_POST, 1);
        curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $curlPost);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
        $ret = curl_exec($curlHandle);
        curl_close($curlHandle);

        return $ret;
    }
}
