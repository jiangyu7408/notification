<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/01
 * Time: 3:41 PM.
 */

namespace Worker\Model;

/**
 * Class ResponseFactory.
 */
class ResponseFactory
{
    /**
     * @param Request $request
     * @param array   $input   [success]
     *
     * @return Response
     */
    public function create(Request $request, array $input)
    {
        $response = new Response($request, $input);

        return $response;
    }
}
