<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/01
 * Time: 3:40 PM.
 */
namespace Worker\Model;

/**
 * Class Response.
 */
class Response
{
    /**
     * @var Request
     */
    public $request;
    /**
     * @var array
     */
    public $info;

    /**
     * @param Request $request
     * @param array   $input
     */
    public function __construct(Request $request, array $input)
    {
        $this->request = $request;
        $this->info = $input;
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->info['http_code'] === 200;
    }

    /**
     * @return string|null
     */
    public function getContent()
    {
        return $this->info['content'];
    }
}
