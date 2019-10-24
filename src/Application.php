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
        $request_method = $this->request->getMethod();
        $post_data = [];

        switch ($request_method) {
            case "POST":
                // check if there are no request parameters
                if (empty($_POST)) {
                    $this->response->setStatusCode(Response::HTTP_BAD_REQUEST);
                    break;
                }

                // get values of request parameters
                $upload = $this->request->request->get('upload');
                $formats = $this->request->request->get('formats');
                $file = $this->request->files->get('file');

                
                $this->response->setStatusCode(Response::HTTP_OK);
                break;
            case "GET":
                $this->response->setStatusCode(Response::HTTP_METHOD_NOT_ALLOWED);
                break;
            default:
                $this->response->setStatusCode(Response::HTTP_BAD_REQUEST);
                break;
        }
        // define the response content
        $response_content = [
            "url" => ""
        ];

        // set response character set as UTF-8 always
        $this->response->setCharset('UTF-8');
        // set response content
        $this->response->setContent(json_encode($response_content));

        return $this->response;
    }
}
