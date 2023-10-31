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

namespace TimoPaul\ProcessingPartners\Models;

class ResultCode
{
    /**
     * @var string
     */
    private string $code;

    /**
     * @var string|null
     */
    private ?string $description;

    /**
     * @param string $code
     * @param string|null $description
     */
    public function __construct(string $code, ?string $description = null)
    {
        $this->code = $code;
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }


    public function isSuccessful(): bool
    {
        return $this->isSuccessfullyProcessed()
            || $this->isSuccessfullyProcessedAndShouldManuallyReviewed()
            || $this->isPending();
    }


    public function isRejected(): bool
    {
        return $this->isRejectedDue3DSecureAndIntercardRiskChecks()
            || $this->isRejectedByExternalBankOrPaymentSystem()
            || $this->isRejectedDueCommunicationErrors()
            || $this->isRejectedDueSystemErrors()
            || $this->isRejectedDueErrorInAsynchonousWorkflow()
            || $this->isSoftDecline()
            || $this->isRejectedDueChecksByExternalRiskSystems()
            || $this->isRejectedDueAddressValidation()
            || $this->isRejectedDue3DSecure()
            || $this->isRejectedDueBlacklistValidation()
            || $this->isRejectedDueRiskValidation()
            || $this->isRejectedDueConfigurationValidation()
            || $this->isRejectedDueRegistrationValidation()
            || $this->isRejectedDueJobValidation()
            || $this->isRejectedDueReferenceValidation()
            || $this->isRejectedDueFormatValidation()
            || $this->isRejectedDueContactValidation()
            || $this->isRejectedDueAccountValidation()
            || $this->isRejectedDueAmountValidation()
            || $this->isRejectedDueRiskManagement();
    }

    /**
     * Returns true if the code is in the following ranges:
     *      000.000.xxx
     *      000.100.1xx
     *      000.3xx.xxx
     *      000.6xx.xxx
     *      000.400.110
     *      000.400.120
     *
     * @return bool
     */
    public function isSuccessfullyProcessed(): bool
    {
        $pattern = '/^(000\.000\.|000\.100\.1|000\.[36]|000\.400\.[1][12]0)/';
        return preg_match($pattern, $this->getCode());
    }

    /**
     * Returns true if the code is in the following ranges:
     *      000.400.01x
     *      000.400.02x
     *      000.400.04x
     *      000.400.05x
     *      000.400.06x
     *      000.400.07x
     *      000.400.08x
     *      000.400.09x
     *      000.400.100
     *
     * @return bool
     */
    public function isSuccessfullyProcessedAndShouldManuallyReviewed(): bool
    {
        $pattern = '/^(000\.400\.0[^3]|000\.400\.100)/';
        return preg_match($pattern, $this->getCode());
    }

    /**
     * Returns true if the code is in the following ranges:
     *      000.200.xxx
     *      100.400.500
     *      800.400.5xx
     *
     * @return bool
     */
    public function isPending(): bool
    {
        $pattern = '/^(000\.200|100\.400\.500|800\.400\.5)/';
        return preg_match($pattern, $this->getCode());
    }

    /**
     * Returns true if the code is in the following ranges:
     *      000.400.1x1
     *      000.400.1x2
     *      000.400.1x3
     *      000.400.1x4
     *      000.400.1x5
     *      000.400.1x6
     *      000.400.1x7
     *      000.400.1x8
     *      000.400.1x9
     *      000.400.2XX
     *
     * @return bool
     */
    public function isRejectedDue3DSecureAndIntercardRiskChecks(): bool
    {
        $pattern = '/^(000\.400\.[1][0-9][1-9]|000\.400\.2)/';
        return preg_match($pattern, $this->getCode());
    }

    /**
     * Returns true if the code is in the following ranges:
     *      800.100.xxx
     *      800.700.xxx
     *      800.800.1xx
     *      800.800.2xx
     *      800.800.3xx
     *
     * @return bool
     */
    public function isRejectedByExternalBankOrPaymentSystem(): bool
    {
        $pattern = '/^(800\.[17]00|800\.800\.[123])/';
        return preg_match($pattern, $this->getCode());
    }

    /**
     * Returns true if the code is in the following ranges:
     *      000.400.030
     *      900.100.xxx
     *      900.200.xxx
     *      900.300.xxx
     *      900.400.xxx
     *
     * @return bool
     */
    public function isRejectedDueCommunicationErrors(): bool
    {
        $pattern = '/^(000\.400\.030|900\.[1234]00)/';
        return preg_match($pattern, $this->getCode());

    }

    /**
     * Returns true if the code is in the following ranges:
     *      800.5xx.xxx
     *      800.6xx.xxx
     *      800.800.4xx
     *      800.800.8xx
     *      999.600.1xx
     *
     * @return bool
     */
    public function isRejectedDueSystemErrors(): bool
    {
        $pattern = '/^(800\.[56]|800\.800\.[48]|999\.|600\.1)/';
        return preg_match($pattern, $this->getCode());
    }

    /**
     * Returns true if the code is in the following ranges:
     *      100.395.xxx
     *      100.396.xxx
     *      100.397.xxx
     *
     * @return bool
     */
    public function isRejectedDueErrorInAsynchonousWorkflow(): bool
    {
        $pattern = '/^(100\.39[567])/';
        return preg_match($pattern, $this->getCode());
    }

    /**
     * Returns true if the code is in the following range:
     *      300.100.100
     *
     * @return bool
     */
    public function isSoftDecline(): bool
    {
        $pattern = '/^(300\.100\.100)/';
        return preg_match($pattern, $this->getCode());
    }

    /**
     * Returns true if the code is in the following ranges:
     *      100.400.0xx
     *      100.400.1xx
     *      100.400.2xx
     *      100.400.3xx
     *      100.380.100
     *      100.380.11x
     *      100.380.4xx
     *      100.380.5xx
     *
     * @return bool
     */
    public function isRejectedDueChecksByExternalRiskSystems(): bool
    {
        $pattern = '/^(100\.400\.[0-3]|100\.380\.100|100\.380\.11|100\.380\.4|100\.380\.5)/';
        return preg_match($pattern, $this->getCode());
    }

    /**
     * Returns true if the code is in the following ranges:
     *      100.800.xxx
     *      800.400.1xx
     *
     * @return bool
     */
    public function isRejectedDueAddressValidation(): bool
    {
        $pattern = '/^(100\.800|800\.400\.1)/';
        return preg_match($pattern, $this->getCode());
    }

    /**
     * Returns true if the code is in the following ranges:
     *      800.400.2xx
     *      100.390.xxx
     *
     * @return bool
     */
    public function isRejectedDue3DSecure(): bool
    {
        $pattern = '/^(800\.400\.2|100\.390)/';
        return preg_match($pattern, $this->getCode());
    }

    /**
     * Returns true if the code is in the following ranges:
     *      800.2xx.xxx
     *      800.3xx.xxx
     *
     * @return bool
     */
    public function isRejectedDueBlacklistValidation(): bool
    {
        $pattern = '/^(800\.[23])/';
        return preg_match($pattern, $this->getCode());
    }

    /**
     * Returns true if the code is in the following ranges:
     *      800.110.xxx
     *      800.120.xxx
     *      800.130.xxx
     *      800.140.xxx
     *      800.150.xxx
     *      800.160.xxx
     *
     * @return bool
     */
    public function isRejectedDueRiskValidation(): bool
    {
        $pattern = '/^(800\.1[123456]0)/';
        return preg_match($pattern, $this->getCode());
    }

    /**
     * Returns true if the code is in the following ranges:
     *      500.1xx.xxx
     *      500.2xx.xxx
     *      600.2xx.xxx
     *      600.3xx.xxx
     *      800.121.xxx
     *
     * @return bool
     */
    public function isRejectedDueConfigurationValidation(): bool
    {
        $pattern = '/^(500\.[12]|600\.[23]|800\.121)/';
        return preg_match($pattern, $this->getCode());
    }

    /**
     * Returns true if the code is in the following ranges:
     *      100.150.xxx
     *      100.350.xxx
     *
     * @return bool
     */
    public function isRejectedDueRegistrationValidation(): bool
    {
        $pattern = '/^(100\.[13]50)/';
        return preg_match($pattern, $this->getCode());
    }

    /**
     * Returns true if the code is in the following ranges:
     *      100.250.xxx
     *      100.360.xxx
     *
     * @return bool
     */
    public function isRejectedDueJobValidation(): bool
    {
        $pattern = '/^(100\.250|100\.360)/';
        return preg_match($pattern, $this->getCode());
    }

    /**
     * Returns true if the code is in the following ranges:
     *      700.100.xxx
     *      700.150.xxx
     *      700.300.xxx
     *      700.350.xxx
     *      700.400.xxx
     *      700.450.xxx
     *      700.500.xxx
     *      700.550.xxx
     *
     * @return bool
     */
    public function isRejectedDueReferenceValidation(): bool
    {
        $pattern = '/^(700\.[1345][05]0)/';
        return preg_match($pattern, $this->getCode());
    }

    /**
     * Returns true if the code is in the following ranges:
     *      100.30x.xxx
     *      100.37x.xxx
     *      100.50x.xxx
     *      100.57x.xxx
     *      100.600.500
     *      100.900.500
     *      200.1xx.xxx
     *      200.2xx.xxx
     *      200.3xx.xxx
     *      800.900.xxx
     *
     * @return bool
     */
    public function isRejectedDueFormatValidation(): bool
    {
        $pattern = '/^(100\.[35][07]|100\.[69]00\.500|200\.[123]|800\.900)/';
        return preg_match($pattern, $this->getCode());
    }

    /**
     * Returns true if the code is in the following ranges:
     *      100.700.xxx
     *      100.900.1xx
     *      100.900.2xx
     *      100.900.3xx
     *      100.900.4xx
     *      100.900.6xx
     *      100.900.7xx
     *      100.900.8xx
     *      100.900.9xx
     *      100.900.0xx
     *
     * @return bool
     */
    public function isRejectedDueContactValidation(): bool
    {
        $pattern = '/^(100\.700|100\.900\.[123467890])/';
        return preg_match($pattern, $this->getCode());
    }

    /**
     * Returns true if the code is in the following ranges:
     *      100.100.xxx
     *      100.20x.xxx
     *      100.21x.xxx
     *
     * @return bool
     */
    public function isRejectedDueAccountValidation(): bool
    {
        $pattern = '/^(100\.100|100.2[01])/';
        return preg_match($pattern, $this->getCode());
    }

    /**
     * Returns true if the code is in the following range:
     *      100.55x.xxx
     *
     * @return bool
     */
    public function isRejectedDueAmountValidation(): bool
    {
        $pattern = '/^(100\.55)/';
        return preg_match($pattern, $this->getCode());
    }

    /**
     * Returns true if the code is in the following ranges:
     *      100.380.2xx
     *      100.380.3xx
     *      100.380.101
     *
     * @return bool
     */
    public function isRejectedDueRiskManagement(): bool
    {
        $pattern = '/^(100\.380\.[23]|100\.380\.101)/';
        return preg_match($pattern, $this->getCode());
    }

    /**
     * Returns true if the code is in the following range:
     *      000.100.2xx
     *
     * @return bool
     */
    private function isChargebackRelated(): bool
    {
        $pattern = '/^(000\.100\.2)/';
        return preg_match($pattern, $this->getCode());
    }

    /**
     * Returns true if the code is in the following range:
     *      000.400.109
     *
     * @return bool
     */
    private function cardIsNotEnrolledFor3dSecureVersion2(): bool
    {
        $pattern = '/^(000\.400\.109)/';
        return preg_match($pattern, $this->getCode());
    }


}