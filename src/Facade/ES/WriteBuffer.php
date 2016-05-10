<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2016/05/10
 * Time: 14:53.
 */
namespace Facade\ES;

use Closure;

/**
 * Class WriteBuffer.
 */
class WriteBuffer
{
    /** @var int */
    protected $bufferLengthMax;
    protected $buffer = [];
    /** @var Closure */
    protected $action;

    /**
     * Manager constructor.
     *
     * @param Closure $action
     * @param int     $bufferLength
     */
    public function __construct(Closure $action, $bufferLength = 500)
    {
        $this->action = $action;
        $this->bufferLengthMax = $bufferLength;
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->sync();
    }

    /**
     * @param array $userInfo
     */
    public function add(array $userInfo)
    {
        $this->buffer[] = $userInfo;
        $currentBufferLength = count($this->buffer);
        if ($currentBufferLength >= $this->bufferLengthMax) {
            $this->sync();
        }
    }

    /**
     *
     */
    public function sync()
    {
        $count = count($this->buffer);
        if ($count === 0) {
            return;
        }

        call_user_func($this->action, $this->buffer);
        $this->buffer = [];
    }
}
