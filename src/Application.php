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
        $request_method = $request->getMethod();
        $response_content = [];
        $url = "";
        try {
            switch ($request_method) {
                case "POST":
                    // check if there are no request parameters

                    // get values of request parameters
                    $upload = $request->get('upload');
                    $formats = $request->get('formats');
                    $file = $request->files->get('file');

                    // check if file is not empty
                    if (!$file || $file == NULL) {
                        return $this->response->setContent(json_encode(array("error" => "File not found")))
                            ->setStatusCode(Response::HTTP_BAD_REQUEST);
                    }

                    // check if file is not empty
                    if (empty($upload) || !in_array($upload, $this->accepted_upload_type)) {
                        return $this->response->setStatusCode(Response::HTTP_BAD_REQUEST);
                    }

                    // add format key to response object
                    if (!empty($formats)) {
                        $response_content["formats"] = $formats;
                    }
                    // upload file
                    $url = $this->handleUpload($upload, $formats, $file);

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
            $response_content = $url;

        } catch (\Exception $exception) {
            $response_content = [
                "url" => $url,
                "exception" => $exception->getMessage()
            ];
            $this->response->setStatusCode(Response::HTTP_BAD_REQUEST);
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
    private function handleUpload(string $upload = '', array $format = [], $file)
    {
        $formatUrls = [];
        if (!empty($format)) {
            foreach ($format as $key => $value) {
                $convertedFile = $this->handleFormatConversion($value, $file);
                $formatUrls[$value] = empty($convertedFile["url"]) ? $this->uploadFile($upload, $convertedFile["file"]) : $convertedFile["url"];
            }
        }
        $url = $this->uploadFile($upload, $file);
        $data["url"] = $url;
        if(!empty($formatUrls))
        {
            $data['formats'] = $formatUrls;
        }
        return $data;
    }

    private function uploadFile($location, $convertedFile)
    {
        switch ($location) {
            case "dropbox":
                $dropbox = new DropboxClient($this->config['dropbox']['access_key'], $this->config['dropbox']['secret_token'], $this->config['dropbox']['container']);
                $url = $dropbox->upload($convertedFile);
                break;
            case "s3":
                $s3 = new \S3Stub\Client($this->config['s3']['access_key_id'], $this->config['s3']['secret_access_key']);
                $fileObj = $s3->send($convertedFile, $this->config['s3']['bucketname']);
                $url = $fileObj->getPublicUrl();
                break;
            case "ftp":
                $ftp = new FTPUploader();
                $ftp->uploadFile($convertedFile, $this->config['ftp']['hostname'], $this->config['ftp']['username'], $this->config['ftp']['password'], $this->config['ftp']['destination']);
                $url = "ftp://" . $this->config['ftp']['hostname']
                    . "/" . $this->config['ftp']['destination'] . "/" . $convertedFile->getFileName();
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
            $fileObj = $convertedFile->convert($file);
            return ["file" => $fileObj, "url" => ""];
        } elseif(!in_array($format, $this->accepted_format)) {
            throw new \Exception( "Invalid file conversion format");
        } else {
            $convertedFile = new Client($this->config['encoding.com']['app_id'], $this->config['encoding.com']['access_token']);
            return ["file" => $file, "url" => $convertedFile->encodeFile($file, $format)];
        }

    }
}
