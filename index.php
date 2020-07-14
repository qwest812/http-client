<?php

class Auth
{
    /**
     * @var array
     */
    private $headers = [];
    /**
     * @var array
     */
    private $responseHeaders = [];
    /**
     * @var bool|string
     */

    public $token = '';

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
     * @param array $arr
     */
    public function addHeaders(array $arr)
    {
        $statPoint = false;
        foreach ($arr as $key => $value) {
            if ($statPoint) {
                $this->headers .= ' ' . $key . ":" . $value;
                $statPoint = true;
            } else {
                $this->headers .= $key . ":" . $value;
            }
        }
    }

    public function sendJSON($data)
    {
        try {
            $data = json_encode($data);
            if (!$this->isJSON($data)) {
                throw new JsonError();
            }
            $this->addHeaders(['Content-Type' => 'application/json', 'Authorization' => $this->token]);
            $this->sendRequest('https://www.coredna.com/assessment-endpoint.php', 'POST', $data);
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
        $this->addHeaders($header);
        $context_options = array(
            'https' => array(
                'method' => $method,
                'header' => $this->headers,
                'content' => $data,
            )
        );
        $context = stream_context_create($context_options);
        $fp = fopen($path, 'r', false, $context);
        if (!$fp) {
            $this->logs("Error fopen: " . $http_response_header()[0]);
        } else {
            fpassthru($fp);
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

    public function getHeaders()
    {
        return $http_response_header;
    }

    /**
     * @return array
     */
    public function getResponseHeaders()
    {
        return $this->responseHeaders;
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
$class->sendJSON(['"name"' => "Iaroslav Rybachuk", "email" => "rybachuk.iaroslav@gmail.com", "url" => "https://github.com/john-doe/http-client"]);

?>