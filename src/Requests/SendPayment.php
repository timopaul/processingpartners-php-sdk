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

class SendPayment extends Request
{
    use IsPostRequest;
    use HasParameters;

    const PARAMETER_AMOUNT = 'amount';
    const PARAMETER_CURRENCY = 'currency';
    const PARAMETER_PAYMENT_BRAND = 'paymentBrand';
    const PARAMETER_PAYMENT_TYPE = 'paymentType';
    const PARAMETER_CARD_NUMBER = 'card.number';
    const PARAMETER_CARD_HOLDER = 'card.holder';
    const PARAMETER_CARD_EXPIRY_MONTH = 'card.expiryMonth';
    const PARAMETER_CARD_EXPIRY_YEAR = 'card.expiryYear';
    const PARAMETER_CARD_CVV = 'card.cvv';

    protected string $urlPath = 'payments';

    /**
     * Returns array of valid request parameters.
     *
     * @return array
     */
    public function getValidParameters(): array
    {
        return array_merge(parent::getValidParameters(), [
            self::PARAMETER_AMOUNT,
            self::PARAMETER_CURRENCY,
            self::PARAMETER_PAYMENT_BRAND,
            self::PARAMETER_PAYMENT_TYPE,
            self::PARAMETER_CARD_NUMBER,
            self::PARAMETER_CARD_HOLDER,
            self::PARAMETER_CARD_EXPIRY_MONTH,
            self::PARAMETER_CARD_EXPIRY_YEAR,
            self::PARAMETER_CARD_CVV,
        ]);
    }
}