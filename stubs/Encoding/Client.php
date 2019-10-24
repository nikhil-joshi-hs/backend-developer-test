<?php
namespace EncodingStub;

class Client
{
    /**
     * @var string
     */
    private $appId;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * Client constructor.
     *
     * @param string $appId
     * @param string $accessToken
     */
    public function __construct(string $appId, string $accessToken)
    {
        $this->appId = $appId;
        $this->accessToken = $accessToken;
    }

    /**
     * "Encodes" a file and returns URL to the result.
     * 
     * @param  \SplFileInfo $file   File to be encoded.
     * @param  string      $format Format to encode to - webm, avi, ogv or mov.
     * @return string URL to the result file.
     */
    public function encodeFile(\SplFileInfo $file, string $format): string
    {
        $format = strtolower($format);

        if (!in_array($format, array('webm', 'avi', 'ogv', 'mov'))) {
            throw new \InvalidArgumentException('Trying to encode to an unsupported video format!');
        }

        return 'http://encoding.com/results/'. $this->appId .'/'. str_replace('.', '_', $file->getFilename()) .'.'. $format;
    }
}