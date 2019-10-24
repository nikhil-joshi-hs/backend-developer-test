<?php

namespace Blossom\BackendDeveloperTest;

use DropboxStub\DropboxClient;
use EncodingStub\Client;
use FFMPEGStub\FFMPEG;
use FTPStub\FTPUploader;
use S3Stub\FileObject;
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

    private $accepted_format;

    private $accepted_upload_type;

    private $config;

    /**
     * Application constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        // set default values.
        $this->http_status = Response::HTTP_OK;
        $this->accepted_format = ['mp4', 'webm', 'ogv'];
        $this->accepted_upload_type = ['dropbox', 's3', 'ftp'];
        $this->config = $config;

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
        $url = "";
        try {
            switch ($request_method) {
                case "POST":
                    // check if there are no request parameters
                    if (empty($_POST)) {
                        return $this->response->setStatusCode(Response::HTTP_BAD_REQUEST);
                    }

                    // get values of request parameters
                    $upload = $this->request->request->get('upload');
                    $formats = $this->request->request->get('formats');
                    $file = $this->request->files->get('file');
                    // upload file
                    $url = $this->handleUpload($upload, $formats, $file);
                    // check if file is not empty
                    if (empty($file)) {
                        return $this->response->setStatusCode(Response::HTTP_BAD_REQUEST);
                    }
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
                "url" => $url
            ];
        } catch (\Exception $exception) {
            $response_content = [
                "url" => $url,
                "exception" => $exception->getMessage()
            ];
        }
        // set response character set as UTF-8 always
        $this->response->setCharset('UTF-8');
        // set response content
        $this->response->setContent(json_encode($response_content));

        return $this->response;
    }

    /**
     * Function to handle file upload to different upload locations
     * @param string $upload
     * @param array $format
     * @param SplFileInfo $file
     */
    private function handleUpload(string $upload = '', array $format = [], SplFileInfo $file)
    {
        if (!empty($format))
        {
            foreach ($format as $key => $value) {
                $convertedFile = $this->handleFormatConversion($value, $file);
                $url = $this->uploadFile($upload, $convertedFile);
            }
        }else{
            $url = $this->uploadFile($upload, $file);
        }

        return $url;
    }

    private function uploadFile($location, $convertedFile)
    {
        switch ($location) {
            case "dropbox":
                $dropbox = new DropboxClient($this->config['dropbox']['accessKey'], $this->config['dropbox']['secretToken'], $this->config['dropbox']['container']);
                $url = $dropbox->upload($convertedFile);
                break;
            case "s3":
                $s3 = new \S3Stub\Client($this->config['s3']['accessKeyId'], $this->config['s3']['secret_access_key']);
                $url = $s3->send($convertedFile, $this->config['s3']['bucketname']);
                break;
            case "ftp":
                $ftp = new FTPUploader();
                $url = $ftp->uploadFile($convertedFile, $this->config['ftp']['hostname'], $this->config['ftp']['username'], $this->config['ftp']['password'], $this->config['ftp']['destination']);
                break;
        }
        return $url;
    }

    /**
     * Function to handle the file format conversion
     * @param $format
     * @param $file
     * @return \SplFileInfo|string
     */
    private function handleFormatConversion($format, $file)
    {
        $convertedFile = $file;
        if ($format == 'mp4') {
            $convertedFile = new FFMPEG();
            return $convertedFile->convert($file);
        } else {
            $convertedFile = new Client($this->config['encoding.com']['app_id'], $this->config['encoding.com']['access_token']);
            return $convertedFile->encodeFile($file, $format);
        }

    }
}
