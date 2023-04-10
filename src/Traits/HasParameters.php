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


namespace TimoPaul\ProcessingPartners\Traits;

use CurlHandle;
use TimoPaul\ProcessingPartners\Exceptions\InvalidParameterException;
use TimoPaul\ProcessingPartners\Exceptions\MissingPropertyException;
use TimoPaul\ProcessingPartners\Exceptions\UnknownParameterException;
use TimoPaul\ProcessingPartners\Request;

trait HasParameters
{
    /**
     * The parameters for the request.
     *
     * @var array
     */
    private array $parameters = [];

    /**
     * Returns array of valid parameters for current Request.
     *
     * @return array
     */
    abstract public function getValidParameters(): array;

    /**
     * Magic method to set parameters dynamically.
     *
     * @param $method
     * @param array $parameters
     * @return mixed
     * @throws InvalidParameterException
     * @throws MissingPropertyException
     */
    public function __call($method, array $parameters) {

        $match = [];
        if (preg_match('#^set([A-Z][A-Za-z0-9]*)$#', $method, $match)) {
            $name = lcfirst($match[1]);
            if ($this->isValidParameter($name)) {
                return $this->addParameter($name, $parameters[0]);
            }
        }

        return is_callable([parent, '__call'])
            ? parent::__call($method, $parameters)
            : call_user_func_array([parent, $method], $parameters);
    }

    /**
     * Returns true if the parameter is valid for this request.
     *
     * @param string $name
     * @return bool
     * @throws MissingPropertyException
     */
    public function isValidParameter(string $name): bool
    {
        return in_array($name, $this->getValidParameters());
    }

    /**
     * Sets the parameters for the request.
     *
     * @param array $parameters
     * @return Request
     * @throws InvalidParameterException
     * @throws MissingPropertyException
     */
    public function setParameters(array $parameters): Request
    {
        return $this->clearParameters()
            ->addParameters($parameters);
    }

    /**
     * Returns the parameters for the request.
     *
     * @return  array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Adds parameters to the request.
     *
     * @param array $parameters
     * @return Request
     * @throws InvalidParameterException
     * @throws MissingPropertyException
     */
    public function addParameters(array $parameters): Request
    {
        foreach ($parameters as $name => $value) {
            $this->addParameter($name, $value);
        }
        return $this;
    }

    /**
     * Adds a parameter to the request.
     *
     * @param string $name
     * @param mixed $value
     * @return Request
     * @throws MissingPropertyException
     * @throws InvalidParameterException
     */
    public function addParameter(string $name, mixed $value): Request
    {
        if ( ! $this->isValidParameter($name)) {
            throw InvalidParameterException::create($name, $this);
        }

        // use boolean url-parameters as integers
        if (is_bool($value)) {
            $value = (int) $value;
        }

        $this->parameters[$name] = $value;
        return $this;
    }

    /**
     * Removes all parameters for this request.
     *
     * @return Request
     */
    public function clearParameters(): Request
    {
        $this->parameters = [];
        return $this;
    }

    /**
     * Returns a parameter.
     *
     * @param string $name
     * @return mixed
     * @throws UnknownParameterException
     */
    public function getParameter(string $name): mixed
    {
        if ( ! isset($this->parameters[$name])) {
            throw UnknownParameterException::create($name);
        }

        return $this->parameters[$name];
    }

    /**
     * Removes a parameter and returns its value.
     *
     * @param string $name
     * @return mixed
     * @throws UnknownParameterException
     */
    public function removeParameter(string $name): mixed
    {
        $value = $this->getParameter($name);
        unset($this->parameters[$name]);
        return $value;
    }

    /**
     * Returns the parameters as URL parameters.
     *
     * @param array|null $forParameters Array of Parameters in the string
     * @return string
     */
    protected function getParameterString(?array $forParameters = null): string
    {
        $parameters = [];
        foreach ($this->getParameters() as $name => $value) {
            if (null !== $forParameters && ! in_array($name, $forParameters)) {
                continue;
            }
            $parameters[] = $name . '=' . urldecode($value);
        }

        if (0 < count($parameters)) {
            return implode('&', $parameters);
        }

        return '';
    }

    /**
     * Adds the parameters for the request to the URL.
     *
     * @param   string $urlPath
     * @return  string
     */
    protected function modifyParametersUrlPath(string $urlPath): string
    {
        $parameters = $this->getParameters();

        // use url-parameters only for get-requests
        if (in_array(IsGetRequest::class, class_uses(static::class))) {
            // replace parameters in the url-path
            foreach ($parameters as $name => $value) {
                $key = sprintf('{%s}', $name);
                if (str_contains($urlPath, $key)) {
                    $urlPath = str_replace($key, $value, $urlPath);
                    unset($parameters[$name]);
                }
            }
            // append other parameters
            if (0 < count($parameters)) {
                $urlPath .= '?' . $this->getParameterString(array_keys($parameters));
            }
        }

        return $urlPath;
    }

    /**
     * Sets the CURL options for a request with URL parameters.
     *
     * @param CurlHandle $curl
     * @return Request
     */
    protected function setParametersCurlOptions(CurlHandle $curl): Request
    {
        // set the parameters for post-requests
        if (in_array(IsPostRequest::class, class_uses(static::class))) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $this->getParameterString());
        }
        return $this;
    }

}