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
 * Interface Client
 * @package arc\http
 */
interface Client
{
    /**
    * Send a HTTP GET request and return the response
    * @param string $url The URL to request
    * @param mixed $request The query paramaters as a string or array
    * @param array $options Any of the HTTP stream context options, e.g. extra headers.
    * @return string
    */
    public function get( $url, $request = null, $options = [] );

    /**
     * Send a HTTP POST request and return the response
     * @param string $url The URL to request
     * @param mixed $request The query paramaters as a string or array
     * @param array $options Any of the HTTP stream context options, e.g. extra headers.
     * @return string
     */
    public function post( $url, $request = null, $options = [] );

    /**
     * Send a HTTP PUT request and return the response
     * @param string $url The URL to request
     * @param mixed $request The query paramaters as a string or array
     * @param array $options Any of the HTTP stream context options, e.g. extra headers.
     * @return string
     */
    public function put( $url, $request = null, $options = [] );

    /**
     * Send a HTTP DELETE request and return the response
     * @param string $url The URL to request
     * @param mixed $request The query paramaters as a string or array
     * @param array $options Any of the HTTP stream context options, e.g. extra headers.
     * @return string
     */
    public function delete( $url, $request = null, $options = [] );

    /**
     * Send a HTTP request and return the response
     * @param string $method The method to use, GET, POST, etc.
     * @param string $url The URL to request
     * @param mixed $request The query paramaters as a string or array
     * @param array $options Any of the HTTP stream context options, e.g. extra headers.
     * @return string
     */
    public function request( $method, $url, $request = null, $options = [] );

    /**
     * Adds headers for subsequent requests
     * @param mixed $headers The headers to add, either as a string or an array of headers.
     * @return $this
     */
    public function headers( $headers );
}
