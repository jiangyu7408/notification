<?php
/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/06/25
 * Time: 11:51 AM
 */

namespace FBGateway;

/**
 * Class FBNotification
 * @package FBGateway
 */
class FBNotification
{
    public $snsid;
    public $template;
    public $trackRef;

    public function toArray()
    {
        return array(
            'snsid'    => $this->snsid,
            'template' => $this->template,
            'trackRef' => $this->trackRef,
        );
    }
}
