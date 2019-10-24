<?php

namespace Blossom\BackendDeveloperTest;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * You should implement this class however you want.
 *
 * The only requirement is existence of public function `handleRequest()`
 * as this is what is tested. The constructor's signature must not be changed.
 */
class Application
{
    /**
     * By default the constructor takes a single argument which is a config array.
     *
     * You can handle it however you want.
     *
     * @param array $config Application config.
     */

    /**
     * @var $request Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * @var $response Symfony\Component\HttpFoundation\Response
     */
    private $response;

    /**
     * @var $http_status
     */
    private $http_status;

    /**
     * Application constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->http_status = Response::HTTP_OK;
        $this->request = new Request(
            $_GET,
            $_POST,
            [],
            $_COOKIE,
            $_FILES,
            $_SERVER
        );

        $this->response = new Response(
            'Content',
            $this->http_status,
            ['content-type' => 'application/json']
        );
    }

    /**
     * This method should handle a Request that comes pre-filled with various data.
     *
     * You should implement it however you want and it should return a Response
     * that passes all tests found in EncoderTest.
     *
     * @param Request $request The request.
     *
     * @return Response
     */
    public function handleRequest(Request $request): Response
    {
        $content = [
            "message" => "",
            "error" => "",
            "success" => true,
            "url" => "string"
        ];
        $this->response->setCharset('UTF-8');
        $this->response->setContent(json_encode($content));
        return $this->response;
    }
}
