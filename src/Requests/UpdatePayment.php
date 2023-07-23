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


namespace TimoPaul\ProcessingPartners\Requests;

use TimoPaul\ProcessingPartners\Request;
use TimoPaul\ProcessingPartners\Traits\HasParameters;
use TimoPaul\ProcessingPartners\Traits\IsPostRequest;

class UpdatePayment extends Request
{
    use HasParameters;
    use IsPostRequest;

    const PARAMETER_REFERENCED_PAYMENT_ID = 'referencedPaymentId';
    const PARAMETER_ENTITY_ID = 'entityId';
    const PARAMETER_TEST_MODE = 'testMode';
    const PARAMETER_AMOUNT = 'amount';
    const PARAMETER_CURRENCY = 'currency';
    const PARAMETER_PAYMENT_TYPE = 'paymentType';

    protected string $urlPath = 'payments/{' . self::PARAMETER_PAYMENT_ID . '}';

    /**
     * Returns array of valid request parameters.
     *
     * @return array
     */
    public function getValidParameters(): array
    {
        return array_merge(parent::getValidParameters(), [
            self::PARAMETER_REFERENCED_PAYMENT_ID,
            self::PARAMETER_ENTITY_ID,
            self::PARAMETER_TEST_MODE,
            self::PARAMETER_AMOUNT,
            self::PARAMETER_CURRENCY,
            self::PARAMETER_PAYMENT_TYPE,
        ]);
    }

}