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
    /** @var TaskProvider */
    protected $taskProvider;
    /** @var int */
    protected $size;
    /** @var resource */
    protected $curl;
    /** @var Request[] */
    protected $queue;
    /** @var ResponseFactory */
    protected $responseFactory;
    /** @var HttpTracer[] */
    protected $trace = [];
    /** @var HttpTracer */
    protected $httpTracer;

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
        $this->responseFactory = new ResponseFactory();
    }

    /**
     *
     */
    public function __destruct()
    {
        if (is_resource($this->curl)) {
            curl_multi_close($this->curl);
        }
    }

    /**
     * @return bool
     */
    public function canAdd()
    {
        return (count($this->queue) <= $this->size);
    }

    /**
     * @param Request $request
     */
    public function add(Request $request)
    {
        if (is_object($this->httpTracer)) {
            $this->trace[$request->url] = $tracer = clone $this->httpTracer;
            $tracer->start();
        }
        $this->queue[$request->url] = $request;
        $ret = curl_multi_add_handle($this->curl, $request->handle);
        assert($ret === 0);
    }

    /**
     * @return \Generator
     */
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

            while (false !== ($done = (curl_multi_info_read($this->curl)))) {
                $response = $this->createResponse($done);
                $this->traceResponse($response);

                yield $response;
            }
        } while ($running);
    }

    /**
     * @param string $url
     *
     * @return Request
     */
    public function get($url)
    {
        assert(array_key_exists($url, $this->queue));

        return $this->queue[$url];
    }

    /**
     * @param HttpTracer $httpTracer
     *
     * @return $this
     */
    public function setTracer(HttpTracer $httpTracer)
    {
        $this->httpTracer = $httpTracer;

        return $this;
    }

    /**
     * @return HttpTracer[]
     */
    public function getTrace()
    {
        return $this->trace;
    }

    /**
     * @param Response $response
     */
    protected function traceResponse(Response $response)
    {
        if (!method_exists($this->httpTracer, 'stop')) {
            return;
        }
        $info = $response->info;
        $url = $info['url'];
        if (!isset($this->trace[$url])) {
            return;
        }
        $this->trace[$url]->stop($info);
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

    /**
     * @param Response $response
     */
    protected function cleanUp(Response $response)
    {
        $ret = curl_multi_remove_handle($this->curl, $response->request->handle);
        if ($ret !== 0) {
            $this->handleCurlError($response);
        }
    }

    /**
     * @param Response $response
     */
    private function handleCurlError(Response $response)
    {
        assert(is_object($response));
        // TODO handle error
    }
}
