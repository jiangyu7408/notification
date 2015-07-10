<?php

/**
 * Created by PhpStorm.
 * User: Jiang Yu
 * Date: 2015/07/01
 * Time: 2:37 PM.
 */

namespace Worker\Queue;

use Worker\Model\Request;
use Worker\Model\Response;
use Worker\Model\ResponseFactory;

/**
 * Class RunningQueue.
 */
class RunningQueue
{
    /**
     * @var Request[]
     */
    protected $queue;
    protected $verbose = true;
    /**
     * @var ResponseFactory
     */
    protected $responseFactory;

    /**
     * @param TaskProvider $taskProvider
     * @param int          $size
     */
    public function __construct(TaskProvider $taskProvider, $size)
    {
        $this->taskProvider = $taskProvider;
        $this->size = $size;
        $this->curl = curl_multi_init();
        $this->queue = [];
        $this->responseFactory = (new ResponseFactory());
    }

    public function __destruct()
    {
        if (is_resource($this->curl)) {
            curl_multi_close($this->curl);
        }
    }

    public function canAdd()
    {
        return (count($this->queue) <= $this->size);
    }

    public function add(Request $request)
    {
        $this->queue[$request->url] = $request;

        echo '.';
        $ret = curl_multi_add_handle($this->curl, $request->handle);
        assert($ret === 0);
    }

    public function run()
    {
        $running = 0;

        do {
            while (($ret = curl_multi_exec($this->curl, $running)) === CURLM_CALL_MULTI_PERFORM) {
                ;
            }
            if ($ret !== CURLM_OK) {
                break;
            }

            while ($done = curl_multi_info_read($this->curl)) {
                $response = $this->createResponse($done);
                yield $response;
            }
        } while ($running);
    }

    /**
     * @param array $input
     *
     * @return \Worker\Model\Response
     */
    protected function createResponse(array $input)
    {
        $curlHandle = $input['handle'];
        $info = curl_getinfo($curlHandle);
        $info['content'] = curl_multi_getcontent($curlHandle);

        $request = $this->get($info['url']);

        $response = $this->responseFactory->create($request, $info);

        return $response;
    }

    public function get($url)
    {
        return $this->queue[$url];
    }

    protected function cleanUp(Response $response)
    {
        $ret = curl_multi_remove_handle($this->curl, $response->request->handle);
        if ($ret !== 0) {
            $this->handleCurlError($response);
        }
    }

    private function handleCurlError(Response $response)
    {
        // TODO handle error
    }
}
