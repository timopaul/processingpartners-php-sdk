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

use TimoPaul\ProcessingPartners\Exceptions\MissingPropertyException;
use TimoPaul\ProcessingPartners\Exceptions\UnauthorizedAccessException;

class Client
{
    /**
     * The URL under which the productive API can be reached at ProcessingPartners.
     *
     * @const string
     */
    const API_LIVE_URL = 'https://eu-prod.oppwa.com/v1/';

    /**
     * The URL under which the sandbox API can be reached for testing at ProcessingPartners.
     *
     * @const string
     */
    CONST API_TEST_URL = 'https://eu-test.oppwa.com/v1/';

    /**
     * The access token for requests to ProcessingPartners.
     *
     * @var string
     */
    protected string $token;

    /**
     * The entity ID for requests to ProcessingPartners.
     *
     * @var string
     */
    protected string $entityId;

    /** @var int|null **/
    protected ?int $httpStatus = null;

    /** @var bool **/
    protected bool $isLiveMode = false;

    /** @var string|null **/
    protected ?string $curlError = null;

    /** @var integer|null **/
    protected ?int $curlErrno = null;


    /**
     * Client constructor.
     *
     * @param string $token
     * @param string $entityId
     * @param bool $live
     */
    public function __construct(
        string $token,
        string $entityId,
        bool $live = false
    )
    {
        $this->setToken($token)
            ->setEntityId($entityId)
            ->setIsLiveMode($live);
    }

    /**
     * Sets the access token and returns the current object.
     *
     * @param string $token
     * @return $this
     */
    protected function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    /**
     * Returns the access token.
     *
     * @return string
     */
    protected function getToken(): string
    {
        return $this->token;
    }

    /**
     * Sets the ID of the entity at ProcessingPartners and returns the current object.
     *
     * @param string $entityId
     * @return $this
     */
    public function setEntityId(string $entityId): self
    {
        $this->entityId = $entityId;
        return $this;
    }

    /**
     * Returns the ID of the entity at ProcessingPartners.
     *
     * @return string
     */
    public function getEntityId(): string
    {
        return $this->entityId;
    }

    /**
     * Sets the HTTP status of the last request and returns the current object.
     *
     * @param int|null $httpStatus
     * @return $this
     */
    protected function setHttpStatus(?int $httpStatus): self
    {
        $this->httpStatus = $httpStatus;
        return $this;
    }

    /**
     * Returns the HTTP status of the last request.
     *
     * @return int|null
     */
    public function getHttpStatus(): ?int
    {
        return $this->httpStatus;
    }

    /**
     * Sets the flag whether the productive environment should be requested from ProcessingPartners
     * and returns the current object.
     *
     * @param bool $live
     * @return $this
     */
    public function setIsLiveMode(bool $live): self
    {
        $this->isLiveMode = $live;
        return $this;
    }

    /**
     * Returns the flag whether the productive environment should be requested from ProcessingPartners.
     *
     * @return bool
     */
    public function getIsLiveMode(): bool
    {
        return $this->isLiveMode;
    }

    /**
     * Sets the CURL error of the last request and returns the current object.
     *
     * @param string|null $curlError
     * @return $this
     */
    protected function setCurlError(?string $curlError): self
    {
        $this->curlError = $curlError;
        return $this;
    }

    /**
     * Returns the CURL error of the last request.
     *
     * @return string|null
     */
    public function getCurlError(): ?string
    {
        return $this->curlError;
    }

    /**
     * Sets the CURL error number of the last request and returns the current object.
     *
     * @param int|null $curlErrno
     * @return $this
     */
    protected function setCurlErrno(?int $curlErrno): self
    {
        $this->curlErrno = $curlErrno;
        return $this;
    }

    /**
     * Returns the CURL error number of the last request.
     *
     * @return int|null
     */
    public function getCurlErrno(): ?int
    {
        return $this->curlErrno;
    }

    /**
     * Generates a new request, sets the token for authentication, and returns this request.
     *
     * @param string $request
     * @return Request
     * @throws UnauthorizedAccessException
     */
    public function generateRequest(string $request): Request
    {
        return (new $request())
            ->addParameter(Request::PARAMETER_ENTITY_ID, $this->getEntityId());
    }

    /**
     * Returns the HTTP headers in an array.
     *
     * @return array
     */
    protected function getReportingHeaders(): array
    {
        return [
            'Authorization:Bearer ' . $this->getToken(),
        ];
    }

    /**
     * Resets the properties of the last request.
     */
    protected function resetStatusProperties(): void
    {
        $this->setHttpStatus(null);
        $this->setCurlErrno(null);
        $this->setCurlError(null);
    }

    /**
     * Generates the URL for a request.
     *
     * @param Request $request
     * @return string
     * @throws MissingPropertyException
     */
    protected function generateRequestUrl(Request $request): string
    {
        if (true === $this->getIsLiveMode()) {
            $baseUrl = static::API_LIVE_URL;
        } else {
            $baseUrl = static::API_TEST_URL;
        }

        return $baseUrl . $request->getUrlPath();
    }

    /**
     * Generates the HTTP header for a request.
     *
     * @param Request $request
     * @return array
     */
    protected function buildHttpHeaders(Request $request): array
    {
        return array_merge($this->getReportingHeaders(), $request->getHttpHeaders());
    }

    /**
     * Send a request to the API and return the result as an object.
     *
     * @param Request $request
     * @param int $tries
     * @return Response|array|null
     * @throws MissingPropertyException
     * @throws UnauthorizedAccessException
     */
    public function sendRequest(Request $request, int $tries = 2)
    {
        $this->resetStatusProperties();

        $url = $this->generateRequestUrl($request);
        $curl = curl_init($url);

        $httpHeaders = $this->buildHttpHeaders($request);
        if (0 < count($httpHeaders)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $httpHeaders);
        }

        $request->setCurlOptions($curl);
        
        curl_setopt($curl, CURLOPT_TIMEOUT, 60); // timeout in seconds
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);

        $this->setHttpStatus((int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE));

        if ('' != curl_error($curl)) {
            $this->setCurlError(curl_error($curl));
            $this->setCurlErrno(curl_errno($curl));
        }
        curl_close($curl);

        if (false === $response && 0 < --$tries && null !== $this->getCurlError()) {
            return $this->sendRequest($request, $tries);
        }

        if (200 != $this->getHttpStatus()) {
            switch ($this->getHttpStatus()) {
                case 502;
                    $this->setCurlError('API down');
                    break;
                case 401:
                    throw UnauthorizedAccessException::create($url);
                default:
                    $this->setCurlError(null);
                    break;
            }
        }

        return $request->buildResponse(json_decode($response));
    }

}