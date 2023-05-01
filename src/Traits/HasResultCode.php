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

use TimoPaul\ProcessingPartners\Exceptions\NoResultCodeAvailableException;
use TimoPaul\ProcessingPartners\Models\ResultCode;

trait HasResultCode
{
    /**
     * Returns the result code of the response.
     *
     * @return ResultCode
     * @throws NoResultCodeAvailableException
     */
    public function getResultCode(): ResultCode
    {
        if (property_exists($this, 'result')
            && $this->result instanceof \stdClass
            && property_exists($this->result, 'code')
        ) {
            return new ResultCode($this->result->code, $this->result->description ?? null);
        }

        throw new NoResultCodeAvailableException($this);
    }
}