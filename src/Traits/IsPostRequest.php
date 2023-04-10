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
use TimoPaul\ProcessingPartners\Request;

trait IsPostRequest
{
    /**
     * Returns the method with which the request should be executed.
     *
     * @return string
     */
    public function getCurlMethod(): string
    {
        return 'POST';
    }

    /**
     * Returns the HTTP headers for a POST request
     *
     * @param CurlHandle $curl
     * @return Request
     */
    protected function setPostRequestCurlOptions(CurlHandle $curl): Request
    {
        curl_setopt($curl, CURLOPT_POST, true);
        return $this;
    }

}