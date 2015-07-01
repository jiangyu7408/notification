<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/01
 * Time: 2:39 PM
 */

namespace Worker\Model;

/**
 * Class RequestFactory
 * @package Worker\Model
 */
class RequestFactory
{
    public function create(array $options)
    {
        $request         = new Request();
        $request->handle = curl_init();
        curl_setopt_array($request->handle, $options);
        $request->options = $options;

        return $request;
    }
}
