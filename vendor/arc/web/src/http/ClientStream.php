<?php

/*
 * This file is part of the Ariadne Component Library.
 *
 * (c) Muze <info@muze.nl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace arc\http;

/**
 * Class ClientStream
 * Implements a HTTP client using PHP's stream handling.
 * @package arc\http
 */
class ClientStream implements Client
{
    private $options        = ['headers' => []];

    public $responseHeaders = null;
    public $requestHeaders  = null;

    /**
     * Merges header string and headers array to single string with all headers
     * @return string
     */
    private function mergeHeaders() {
        $args   = func_get_args();
        $result = '';
        foreach ( $args as $headers ) {
            if (is_array($headers) || $headers instanceof \ArrayObject ) {
                $result .= array_reduce( (array) $headers, function($carry, $entry) {
                    return $carry . "\r\n" . $entry;
                }, '');
            } else {
                $result .= (string) $headers;
            }
        }
        if (substr($result, -2)!="\r\n") {
            $result .= "\r\n";
        }
        return $result;
    }

    /**
     * Send a HTTP request and return the response
     * @param string       $type    The method to use, GET, POST, etc.
     * @param string       $url     The URL to request
     * @param array|string $request The query string
     * @param array        $options Any of the HTTP stream context options, e.g. extra headers.
     * @return string
     */
    public function request( $type, $url, $request = null, $options = [] )
    {
        $url = \arc\url::url( (string) $url);
        if ($type == 'GET' && $request) {
            $url->query->import( $request);
            $request = null;
        }

        $options = [
            'method'  => $type,
            'content' => $request
        ] + $options;

        $options['headers'] = $this->mergeHeaders(
            \arc\hash::get('header', $this->options),
            \arc\hash::get('headers', $this->options),
            \arc\hash::get('header', $options),
            \arc\hash::get('headers', $options)
        );

        $options += (array) $this->options;

        $context = stream_context_create( [ 'http' => $options ] );
        $result  = @file_get_contents( (string) $url, false, $context );
        $this->responseHeaders = isset($http_response_header) ? $http_response_header : null; //magic php variable set by file_get_contents.
        $this->requestHeaders  = isset($options['headers']) ? explode("\r\n",$options['headers']) : [];

        return $result;
    }

    /**
     * @param array $options Any of the HTTP stream context options, e.g. extra headers.
     */
    public function __construct( $options = [] )
    {
        $this->options = $options;
    }

    /**
     * Send a GET request
     * @param string         $url     The URL to request
     * @param array|string   $request The query string
     * @param array          $options Any of the HTTP stream context options, e.g. extra headers.
     * @return string
     */
    public function get( $url, $request = null, $options = [] )
    {
        return $this->request( 'GET', $url, $request, $options );
    }

    /**
     * Send a POST request
     * @param string         $url     The URL to request
     * @param array|string   $request The query string
     * @param array          $options Any of the HTTP stream context options, e.g. extra headers.
     * @return string
     */
    public function post( $url, $request = null, $options = [] )
    {
        return $this->request( 'POST', $url, $request, $options );
    }

    /**
     * Send a PUT request
     * @param string         $url     The URL to request
     * @param array|string   $request The query string
     * @param array          $options Any of the HTTP stream context options, e.g. extra headers.
     * @return string
     */
    public function put( $url, $request = null, $options = [] )
    {
        return $this->request( 'PUT', $url, $request, $options );
    }

    /**
     * Send a DELETE request
     * @param string         $url     The URL to request
     * @param array|string   $request The query string
     * @param array          $options Any of the HTTP stream context options, e.g. extra headers.
     * @return string
     */
    public function delete( $url, $request = null, $options = [] )
    {
        return $this->request( 'DELETE', $url, $request, $options );
    }


    /**
     * Adds headers for subsequent requests
     * @param mixed $headers The headers to add, either as a string or an array of headers.
     * @return $this
     */
    public function headers($headers)
    {
        if (!isset($this->options['headers'])) {
            $this->options['headers'] = [];
        }
        if ( !is_array($headers) ) {
            $headers = explode("\r\n",$headers);
            if (end($headers) == '') {
                array_pop($headers);
            }
        }

        $this->options['headers'] = array_merge($this->options['headers'], $headers);

        return $this;
    }
}
