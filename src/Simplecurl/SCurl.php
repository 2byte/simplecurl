<?php namespace Simplecurl;


/**
 * Simple wrapper curl for php
 *
 * Class SCurl
 * @package simplecurl
 */

class SCurl {

    /**
     * Current curl resource
     *
     * @var resource
     */
    private $ch;
    /**
     * Options a curl
     *
     * @var array
     */
    private $options = [];
    /**
     * Cookies
     *
     * @var
     */
    private $cookies = [];
    /**
     * Callback function upon successfull request
     *
     * @var callback
     */
    private $call_success;
    /**
     * Callback function upon error request
     *
     * @var callback
     */
    private $call_error;

    /**
     * Curl errno
     *
     * @var integer
     */
    public $errno;
    /**
     * Curl error message
     *
     * @var string
     */
    public $error;
    /**
     * Result method exec
     *
     * @var object(CurlRespone)
     */
    public $result = null;

    public static function __callStatic($name, $arguments)
    {
        if ($name == 'instance')
            return call_user_func(array(new self(), 'init'));
    }

    function __construct()
    {
        return $this->init();
    }

    /**
     * Initialize curl and options set default
     * @return $this
     */
    private function init()
    {
        if (!extension_loaded('curl')) {
            throw new \ErrorException('cURL library is not loaded');
        }

        $this->ch = curl_init();

        $this->setDefaultOptions();

        return $this;
    }

    /**
     * Get instance a class upon call static
     *
     * @return $this
     */
    private function instance()
    {
        $this->init();

        return new self();
    }


    /**
     * Request get
     *
     * @param $url
     * @param array $vars
     * @param array $options
     * @return $this->request()
     */
    public function get($url, $vars = null, $options = [])
    {
        if (is_array($vars) && !$this->isSendFile($vars)) $vars = $this->postfieldsArrayToString($vars);

        return $this->request('GET', $url, $vars, $options);
    }

    /**
     * Request post
     *
     * @param $url
     * @param array $vars
     * @param array $options
     * @return $this->request()
     */
    public function post($url, $vars = null, $options = [])
    {
        if (is_array($vars) && !$this->isSendFile($vars)) $vars = $this->postfieldsArrayToString($vars);

        return $this->request('POST', $url, $vars, $options);
    }

    /**
     * Builder request
     *
     * @param string $method
     * @param $url
     * @param array $vars
     * @param array $options
     * @return $this
     */
    public function request($method = 'GET', $url, $vars = null, $options = [])
    {
        $this->createOptions($options);

        $this->createOptions([
            CURLOPT_URL => $url . (!empty($vars) && $method == 'GET' ? '?'. http_build_query($vars, '', '&') : '')
        ]);

        if ($method == 'POST') {
            $this->createOptions([
                CURLOPT_POST        => 1,
                CURLOPT_POSTFIELDS  => $vars
            ]);
        }

        return $this;
    }

    /**
     * Set http headers ->setHeader('X-Requested-With', 'XMLHttpRequest') or ->setRequest(['X-Requested-With:XMLHttpRequest'])
     *
     * @param $header
     * @param null $value
     * @return $this
     */
    public function setHeader($header, $value = null)
    {
        if (is_array($header)) {
            $this->createOptions([CURLOPT_HTTPHEADER => $header]);
        }
        else {
            $this->createOptions([CURLOPT_HTTPHEADER    => [$header .':'. $value]]);
        }

        return $this;
    }

    /**
     * Set user agent header
     *
     * @param $user_agent
     * @return $this
     */
    public function setUserAgent($user_agent)
    {
        $this->createOptions([CURLOPT_USERAGENT => $user_agent]);

        return $this;
    }

    /**
     * Set http header referer
     *
     * @param $url
     * @return $this
     */
    public function setReferer($url)
    {
        $this->createOptions([CURLOPT_REFERER   => $url]);

        return $this;
    }

    /**
     * Set curl option autoreferer
     * @return $this
     */
    public function setAutoreferer()
    {
        $this->createOptions([CURLOPT_AUTOREFERER   => true]);

        return $this;
    }

    /**
     * Set cookie file to CURLOPT_COOKIEFILE and CURLOPT_COOKIEJAR
     *
     * @param $path_file
     * @return $this
     */
    public function setCookieFile($path_file)
    {
        $this->createOptions([
            CURLOPT_COOKIEFILE  => $path_file,
            CURLOPT_COOKIEJAR   => $path_file
        ]);

        return $this;
    }

    /**
     * Set cookie, key,value or array [key => val, key => val]
     * @param $key
     * @param $val
     */
    public function setCookie($key, $val)
    {
        if (is_array($key)) {

            foreach ($key as $k => $v) {
                $this->cookies[$k] = $v;
            }

        }
        else {
            $this->cookies[$key] = $val;
        }
    }

    /**
     * Set cookie string name=val;name=val;name=val
     * @param $string_cookie
     * @return $this
     */
    public function setCookieString($string_cookie)
    {
        $cookie_key_val = explode(';', $string_cookie);

        foreach ($cookie_key_val as $cookie_line) {
            $cookie = explode('=', $cookie_line);

            $this->cookies[$cookie[0]] = $cookie[1];
        }

        return $this;
    }

    /**
     * Set http header ajax request
     * @return $this
     */
    public function setAjax()
    {
        $this->setHeader(['X-Requested-With:XMLHttpRequest']);

        return $this;
    }

    /**
     * Set options CURLOPT_FOLLOWLOCATION
     * @param bool $val
     * @return $this
     */
    public function setLocation($val = true)
    {
        $this->createOptions([CURLOPT_FOLLOWLOCATION    => $val]);

        return $this;
    }

    /**
     * Set default options
     * @return $this
     */
    public function setDefaultOptions()
    {
        $curl_opt_default = [
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_SSL_VERIFYPEER  => 0,
            CURLOPT_SSL_VERIFYHOST  => 0,
            CURLOPT_CONNECTTIMEOUT  => 30,
            CURLOPT_TIMEOUT         => 30
        ];

        $this->options = $curl_opt_default;

        return $this;
    }

    /**
     * Add options
     * @param array $vars
     * @return $this
     */
    public function createOptions($vars = [])
    {
        $this->options = $this->options + $vars;

        return $this;
    }

    /**
     * Run
     */
    public function exec()
    {
        if (!empty($this->cookies)) {
            $cookie_string = '';
            foreach ($this->cookies as $key => $val) {
                $cookie_string .= $key .'='. $val .';';
            }

            if (!empty($cookie_string))
                $this->createOptions([CURLOPT_COOKIE => $cookie_string]);
        }

        curl_setopt_array($this->ch, $this->options);

        $this->result = curl_exec($this->ch);

        if ($this->result) {
            $this->result = new CurlResponse($this->result);
            $this->result->info = curl_getinfo($this->ch);

            return $this->result;
        }

        if ($errno = curl_errno($this->ch))
            $this->errno = $errno;
        if ($error = curl_error($this->ch))
            $this->error = $error;

        curl_close($this->ch);

        if (!$this->call_error AND !$this->call_success AND !$this->errno)
            return $this->result;

        if ($this->call_success)
            return call_user_func($this->call_success, $this->result);
        if ($this->call_error)
            return call_user_func($this->call_error, (object)['errno' => $this->errno, 'error' => $this->error]);

        return $this;
    }

    /**
     * Callback function upon successfull
     * @param $callback
     * @return $this
     */
    public function success($callback)
    {
        $this->call_success = $callback;

        return $this;
    }

    /**
     * Callback function upon error request
     * @param $callback
     * @return $this
     */
    public function error($callback)
    {
        $this->call_error = $callback;

        return $this;
    }

    /**
     * Postfields array to string
     *
     * @param $post_data
     * @return string
     */
    public function postfieldsArrayToString($post_data)
    {
        $postfields = [];

        foreach ($post_data as $key => $val) {
            $postfields[] = sprintf('%s=%s', $key, $val);
        }

        return implode('&', $postfields);
    }

    /**
     * Is send file on post vars
     * @param $vars
     * @return bool
     */
    public function isSendFile($vars)
    {
        foreach ($vars as $key => $val) {
            if (strpos($key, '@') !== false) return true;
        }

        return false;
    }

    function __toString()
    {
        return (string)$this->result;
    }
}