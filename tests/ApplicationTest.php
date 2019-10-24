<?php
namespace Blossom\BackendDeveloperTest\Tests;

use PHPUnit\Framework\TestCase;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Blossom\BackendDeveloperTest\Application;

class ApplicationTest extends TestCase
{
    /**
     * Builds an application instance for all tests.
     *
     * Overwrite it if you need to send any more arguments to the Application's constructor.
     * 
     * @return Application
     */
    protected function provideApplication()
    {
        return new Application([
            'ftp' => [
                'hostname' => 'uploads.blossomeducational.com',
                'username' => 'tester',
                'password' => 'encoder',
                'destination' => 'videos'
            ],
            's3' => [
                'access_key_id' => 'accessKeyId123',
                'secret_access_key' => 'secretTokenOfTeh31337',
                'bucketname' => 'blossom-uploads'
            ],
            'dropbox' => [
                'access_key' => 'fsghfdigsdfgs',
                'secret_token' => 'sgfdgsu43rg',
                'container' => 'blossom-video-uploads'
            ],
            'encoding.com' => [
                'app_id' => 'blossom-fgdfgg87gf7d',
                'access_token' => '234556fghdfgsdfsehery234'
            ]
        ]);
    }

    /************************************
     * WRITE YOUR OWN TESTS HERE
     ************************************/



    /************************************
     * DO NOT CHANGE ANYTHING BELOW
     ************************************/

    public function testReturningResponse()
    {
        $app = $this->provideApplication();

        $request = $this->createMock(Request::class);
        $response = $app->handleRequest($request);

        $this->assertInstanceOf(Response::class, $response, 'Application should always return Response object');
    }

    public function testUploadingToFTP()
    {
        $app = $this->provideApplication();

        $request = Request::create('/', 'POST', [
            'upload' => 'ftp',
            'formats' => []
        ], [], [
            'file' => $this->provideUploadedFile('TechnologyRace.mp4')
        ]);

        $response = $app->handleRequest($request);

        $data = $this->validateResponse($response);

        $this->assertEquals('ftp://uploads.blossomeducational.com/videos/TechnologyRace.mp4', $data['url']);
    }

    public function testUploadingToS3()
    {
        $app = $this->provideApplication();

        $request = Request::create('/', 'POST', [
            'upload' => 's3',
            'formats' => []
        ], [], [
            'file' => $this->provideUploadedFile('TechnologyRace.mp4')
        ]);

        $response = $app->handleRequest($request);

        $data = $this->validateResponse($response);

        $this->assertEquals('http://blossom-uploads.s3.amazonaws.com/TechnologyRace.mp4', $data['url']);
    }

    public function testUploadingToDropbox()
    {
        $app = $this->provideApplication();

        $request = Request::create('/', 'POST', [
            'upload' => 'dropbox',
            'formats' => []
        ], [], [
            'file' => $this->provideUploadedFile('TechnologyRace.mp4')
        ]);

        $response = $app->handleRequest($request);

        $data = $this->validateResponse($response);

        $this->assertEquals('http://uploads.dropbox.com/blossom-video-uploads/TechnologyRace.mp4', $data['url']);
    }

    public function testEncodingToWebm()
    {
        $app = $this->provideApplication();

        $request = Request::create('/', 'POST', [
            'upload' => 's3',
            'formats' => ['webm']
        ], [], [
            'file' => $this->provideUploadedFile('TechnologyRace.mp4')
        ]);

        $response = $app->handleRequest($request);

        $data = $this->validateResponse($response, true);

        $this->assertEquals('http://encoding.com/results/blossom-fgdfgg87gf7d/TechnologyRace_mp4.webm', $data['formats']['webm']);
    }

    public function testEncodingToMp4AndUploadingToS3()
    {
        $app = $this->provideApplication();

        $request = Request::create('/', 'POST', [
            'upload' => 's3',
            'formats' => ['mp4']
        ], [], [
            'file' => $this->provideUploadedFile('TechnologyRace.mp4')
        ]);

        $response = $app->handleRequest($request);

        $data = $this->validateResponse($response, true);

        $this->assertEquals('http://blossom-uploads.s3.amazonaws.com/TechnologyRace_mp4.encoded.mp4', $data['formats']['mp4']);
    }

    public function testUploadingToDropboxAndEncodingToWebmAndMp4()
    {
        $app = $this->provideApplication();

        $request = Request::create('/', 'POST', [
            'upload' => 'dropbox',
            'formats' => ['webm', 'mp4']
        ], [], [
            'file' => $this->provideUploadedFile('TechnologyRace.mp4')
        ]);

        $response = $app->handleRequest($request);

        $data = $this->validateResponse($response, true);

        $this->assertContains('http://uploads.dropbox.com/blossom-video-uploads/TechnologyRace.mp4', $data['url']);
        $this->assertEquals('http://encoding.com/results/blossom-fgdfgg87gf7d/TechnologyRace_mp4.webm', $data['formats']['webm']);
        $this->assertEquals('http://uploads.dropbox.com/blossom-video-uploads/TechnologyRace_mp4.encoded.mp4', $data['formats']['mp4']);
    }
    
    public function testReturns400ForRequestWithoutFile()
    {
        $app = $this->provideApplication();

        $request = Request::create('/', 'POST', [
            'upload' => 'rackspace',
            'formats' => ['webm', 'mp4']
        ], [], []);

        $response = $app->handleRequest($request);

        $this->assertInstanceOf(Response::class, $response, 'Application should always return Response object');

        $this->assertEquals(400, $response->getStatusCode(), 'Requests without uploaded file should return HTTP Code 400 in the response.');
    }

    public function testReturns400ForRequestWithoutParameters()
    {
        $app = $this->provideApplication();

        $request = Request::create('/', 'POST', [], [], [
            'file' => $this->provideUploadedFile('TechnologyRace.mp4')
        ]);

        $response = $app->handleRequest($request);

        $this->assertInstanceOf(Response::class, $response, 'Application should always return Response object');

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), 'Requests without parameters should return HTTP Code 400 in the response.');
    }

    public function testReturns400ForRequestToUnknownService()
    {
        $app = $this->provideApplication();

        $request = Request::create('/', 'POST', [
            'upload' => 'unknown',
            'formats' => ['webm', 'mp4']
        ], [], [
            'file' => $this->provideUploadedFile('TechnologyRace.mp4')
        ]);

        $response = $app->handleRequest($request);

        $this->assertInstanceOf(Response::class, $response, 'Application should always return Response object');

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), 'GET requests should return HTTP Code 405 in the response.');
    }

    public function testReturns400ForRequestWithUnsupportedFormat()
    {
        $app = $this->provideApplication();

        $request = Request::create('/', 'POST', [
            'upload' => 'dropbox',
            'formats' => ['gif']
        ], [], [
            'file' => $this->provideUploadedFile('TechnologyRace.mp4')
        ]);

        $response = $app->handleRequest($request);

        $this->assertInstanceOf(Response::class, $response, 'Application should always return Response object');

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode(), 'Requests with unsupported should return HTTP Code 400 in the response.');
    }

    public function testReturns405ForGETRequest()
    {
        $app = $this->provideApplication();

        $request = Request::create('/', 'GET', [
            'upload' => 'ftp',
            'formats' => ['webm', 'mp4']
        ], [], [
            'file' => $this->provideUploadedFile('TechnologyRace.mp4')
        ]);

        $response = $app->handleRequest($request);

        $this->assertInstanceOf(Response::class, $response, 'Application should always return Response object');

        $this->assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $response->getStatusCode(), 'GET requests should return HTTP Code 405 in the response.');
    }

    /**
     * @param Response $response
     * @param bool $hasFormats
     *
     * @return mixed
     */
    protected function validateResponse($response, bool $hasFormats = false): array
    {
        $this->assertInstanceOf(Response::class, $response, 'Application should always return Response object');

        $this->assertEquals(200, $response->getStatusCode(), 'Valid responses should return HTTP Code 200');
        $this->assertEquals('UTF-8', $response->getCharset(), 'The response should always have UTF-8 charset set');
        $this->assertStringStartsWith('application/json', $response->headers->get('Content-Type'), 'The response should have a Content-Type header set to "application/json"');

        $data = json_decode($response->getContent(), true);

        $this->assertInternalType('array', $data, 'The response should contain a valid JSON string');

        $this->assertArrayHasKey('url', $data, 'The response should contain "url" key with a URL string to where the original file was uploaded');
        $this->assertInternalType('string', $data['url'], 'The response should contain "url" key with a URL string to where the original file was uploaded');

        if ($hasFormats) {
            $this->assertArrayHasKey('formats', $data, 'If there were encoding formats requested then the response should contain a "formats" key.');
            $this->assertInternalType('array', $data['formats'], 'If there were encoding formats requested then the response should contain an array under "formats" key.');
            $this->assertNotEmpty($data['formats'], 'If there were encoding formats requested then the response should contain a non-empty array under "formats" key.');
        }

        return $data;
    }

    /**
     * @param string $originalName
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|UploadedFile
     */
    protected function provideUploadedFile(string $originalName = 'TechnologyRace.mp4')
    {
        $file = $this->getMockBuilder(UploadedFile::class)
            ->setConstructorArgs([__DIR__ . '/fixtures/TechnologyRace.mp4', $originalName, null, null, null, true])
            ->setMethods(null);

        return $file->getMock();
    }
}
