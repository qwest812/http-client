<?php

class Auth
{
    /**
     * @var array
     */
    private $headers = '';
    /**
     * @var array
     */
    private $responseHeaders = [];
    /**
     * @var bool|string
     */

    private $token = '';

    /**
     * Auth constructor.
     */
    public function __construct()
    {
        $this->token =
        $opts = array(
            'http' => array(
                'method' => "OPTIONS",
            )
        );

        $context = stream_context_create($opts);
        $fp = fopen('https://www.coredna.com/assessment-endpoint.php', 'r', false, $context);
        if (!$fp) {
            $this->logs("Error fopen: " . $http_response_header()[0]);
        } else {
            $this->token = fgets($fp, 999);
            $this->responseHeaders = $http_response_header;
        }
        fclose($fp);
    }

    /**
     * @param $path
     * @param $data
     */

    public function sendJSON($path, $data)
    {
        try {
            $data = json_encode($data);
            if (!$this->isJSON($data)) {
                throw new JsonError();
            }
            $this->sendRequest($path, 'POST', $data, ['Authorization' => 'Bearer ' . $this->token, 'Content-Type' => 'application/json']);
        } catch (JsonError $error) {
            echo $error->getMessage();
            $this->logs($error->getMessage());
        }
    }

    /**
     * @param $text
     */
    public function logs($text)
    {
        file_put_contents('log.txt',
            date("H:i:s") . $text . PHP_EOL,
            FILE_APPEND);
    }

    /**
     * @param $path
     * @param $method
     * @param string $data
     * @param array $header
     */
    protected function sendRequest($path, $method, $data = '', $header = [])
    {

        $this->setHeaders($header);
        $context = stream_context_create(array(
            'http' => array(
                'method' => $method,
                'header' => $this->getHeaders(),
                'content' => $data,
            )
        ));

        $fp = fopen($path, 'r', false, $context);
        if (!$fp) {
            $this->logs("Error fopen: " . $http_response_header[0]);
        } else {
            echo 'Success';
        }
    }

    /**
     * @param $string
     * @return bool
     */
    function isJSON($string)
    {
        return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;

    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    public function setHeaders(array $arr)
    {
        $this->headers = '';
        foreach ($arr as $key => $value) {
            $this->headers .= PHP_EOL . $key . ":" . $value;
        }
    }

    /**
     * @return array
     */
    public function getResponseHeaders()
    {
        return $this->responseHeaders;
    }

    /**
     * @param $headers
     */
    public function setResponseHeaders($headers)
    {
        $this->responseHeaders = $headers;
    }
}

/**
 * Class JsonError
 */
class JsonError extends Exception
{
    public function __construct()
    {
        parent::__construct("file isn't array");
    }
}

$class = new Auth();

$class->sendJSON('https://www.coredna.com/assessment-endpoint.php',
    ["name" => "Iaroslav Rybachuk",
    "email" => "rybachuk.iaroslav@gmail.com",
    "url" => "https://github.com/qwest812/http-client"]);
