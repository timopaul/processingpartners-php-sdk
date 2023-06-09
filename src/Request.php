<?php
/*
   Copyright 2023 Timo Paul Dienstleistungen

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*/


namespace TimoPaul\ProcessingPartners;

use stdClass;
use TimoPaul\ProcessingPartners\Exceptions\MissingPropertyException;
use TimoPaul\ProcessingPartners\Traits\HasParameters;

abstract class Request
{
    use HasParameters;

    const PARAMETER_ENTITY_ID = 'entityId';

    /**
     * Path from the URL for the request.
     *
     * @var string
     */
    protected string $urlPath;

    /**
     * Name of the class returned as a response.
     *
     * @var string
     */
    protected string $responseClass = Response::class;

    /**
     * Returns the method with which the request should be executed.
     *
     * @return string
     */
    abstract public function getCurlMethod(): string;

    /**
     * Returns the name of the class returned as a response.
     *
     * @return string
     */
    protected function getResponseClass(): string
    {
        return $this->responseClass;
    }

    /**
     * Transforms the stdClass to `Response` this `Request`.
     *
     * @param stdClass $stdClass
     * @return Response
     */
    public function buildResponse(stdClass $stdClass): Response
    {
        $responseClassName = $this->getResponseClass();

        return unserialize(sprintf(
            'O:%d:"%s"%s',
            strlen($responseClassName),
            $responseClassName,
            strstr(strstr(serialize($stdClass), '"'), ':')
        ));
    }

    /**
     * Returns array of valid parameters for current Request.
     *
     * @return array
     */
    public function getValidParameters(): array
    {
        return [
            self::PARAMETER_ENTITY_ID,
        ];
    }

    /**
     * Returns the path from the URL for the request.
     *
     * @return string
     * @throws MissingPropertyException
     */
    public function getUrlPath(): string
    {
        if ( ! isset($this->urlPath)) {
            throw MissingPropertyException::create('urlPath', $this);
        }

        $urlPath = $this->urlPath;

        foreach ($this->getUrlPathModificationMethods() as $method) {
            $urlPath = $this->$method($urlPath);
        }

        return $urlPath;
    }

    /**
     * Collects all methods of the traits used to modify the url path and returns them.
     *
     * @return array
     */
    protected function getUrlPathModificationMethods(): array
    {
        $methods = [];
        foreach (class_uses(static::class) as $trait) {
            $method = $this->makeUrlPathModificationMethodName($trait);

            if (null !== $method && null !== $methods && method_exists($this, $method)) {
                $methods[] = $method;
            }
        }
        return $methods;
    }

    /**
     * Generates the valid name for the method for modify the url path from a trait used.
     *
     * @param string $trait
     * @return string|null
     */
    protected function makeUrlPathModificationMethodName(string $trait): ?string
    {
        $matches = [];
        $basename = basename(str_replace('\\', '/', $trait));
        if (preg_match('#^Has([A-Z][A-Za-z]+)$#', $basename, $matches)) {
            return 'modify' . $matches[1] . 'UrlPath';
        }
        return null;
    }

    /**
     * Generates the HTTP headers for the request and returns them as an array.
     *
     * @return  array
     */
    public function getHttpHeaders(): array
    {
        $headers = [];
        foreach ($this->getHttpHeaderMethods() as $method) {
            $headers = array_merge($headers, $this->$method());
        }
        return $headers;
    }

    /**
     * Collects all methods of the traits used to compile the HTTP headers and returns them.
     *
     * @return array
     */
    protected function getHttpHeaderMethods(): array
    {
        $methods = [];
        foreach (class_uses(static::class) as $trait) {
            $method = $this->makeHttpHeaderMethodName($trait);

            if (null !== $methods && method_exists($this, $method)) {
                $methods[] = $method;
            }
        }
        return $methods;
    }

    /**
     * Generates the valid name for the method for returning the HTTP headers from a trait used.
     *
     * @param string $trait
     * @return string|null
     */
    protected function makeHttpHeaderMethodName(string $trait): ?string
    {
        $matches = [];
        $basename = basename(str_replace('\\', '/', $trait));
        if (preg_match('#^(Has|Is)([A-Z][A-Za-z]+)$#', $basename, $matches)) {
            return 'get' . $matches[2] . 'HttpHeader';
        }
        return null;
    }

    /**
     * Sets the curl options of this request and all traits used.
     *
     * @param resource $curl
     * @return Request
     */
    public function setCurlOptions($curl): Request
    {
        if (method_exists($this, 'getCurlMethod')) {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->getCurlMethod());
        }

        foreach ($this->getSetCurlOptionsMethods() as $method) {
            $this->$method($curl);
        }

        return $this;
    }

    /**
     * Collects all methods of the traits used to set the curl options and returns them.
     *
     * @return array
     */
    protected function getSetCurlOptionsMethods(): array
    {
        $methods = [];

        foreach (class_uses(static::class) as $trait) {
            $method = $this->makeSetcurlOptionsMethodName($trait);
            if (null !== $methods && method_exists($this, $method)) {
                $methods[] = $method;
            }
        }

        return $methods;
    }

    /**
     * Generates the valid name for the method used to set the curl options from a trait used.
     *
     * @param string $trait
     * @return string|null
     */
    protected function makeSetCurlOptionsMethodName(string $trait): ?string
    {
        $matches = [];
        $basename = basename(str_replace('\\', '/', $trait));
        if (preg_match('#^(Has|Is)([A-Z][A-Za-z]+)$#', $basename, $matches)) {
            return 'set' . $matches[2] . 'CurlOptions';
        }
        return null;
    }
}