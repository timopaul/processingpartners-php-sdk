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


    public function isSuccessfullyProcessed(): bool
    {
        $pattern = '/^(000\.000\.|000\.100\.1|000\.[36]|000\.400\.[1][12]0)/';
        return preg_match($pattern, $this->getCode());
    }


    public function isSuccessfullyProcessedAndShouldManuallyReviewed(): bool
    {
        $pattern = '/^(000\.400\.0[^3]|000\.400\.100)/';
        return preg_match($pattern, $this->getCode());
    }


    public function isPending(): bool
    {
        $pattern = '/^(000\.200|100\.400\.500|800\.400\.5)/';
        return preg_match($pattern, $this->getCode());
    }


    public function isRejectedDue3DSecureAndIntercardRiskChecks(): bool
    {
        $pattern = '/^(000\.400\.[1][0-9][1-9]|000\.400\.2)/';
        return preg_match($pattern, $this->getCode());
    }


    public function isRejectedByExternalBankOrPaymentSystem(): bool
    {
        $pattern = '/^(800\.[17]00|800\.800\.[123])/';
        return preg_match($pattern, $this->getCode());
    }


    public function isRejectedDueCommunicationErrors(): bool
    {
        $pattern = '/^(900\.[1234]00|000\.400\.030)/';
        return preg_match($pattern, $this->getCode());

    }


    public function isRejectedDueSystemErrors(): bool
    {
        $pattern = '/^(800\.[56]|999\.|600\.1|800\.800\.[84])/';
        return preg_match($pattern, $this->getCode());
    }


    public function isRejectedDueErrorInAsynchonousWorkflow(): bool
    {
        $pattern = '/^(100\.39[765])/';
        return preg_match($pattern, $this->getCode());
    }


    public function isSoftDecline(): bool
    {
        $pattern = '/^(300\.100\.100)/';
        return preg_match($pattern, $this->getCode());
    }


    public function isRejectedDueChecksByExternalRiskSystems(): bool
    {
        $pattern = '/^(100\.400\.[0-3]|100\.380\.100|100\.380\.11|100\.380\.4|100\.380\.5)/';
        return preg_match($pattern, $this->getCode());
    }


    public function isRejectedDueAddressValidation(): bool
    {
        $pattern = '/^(100\.800|800\.400\.1)/';
        return preg_match($pattern, $this->getCode());
    }


    public function isRejectedDue3DSecure(): bool
    {
        $pattern = '/^(800\.400\.2|100\.390)/';
        return preg_match($pattern, $this->getCode());
    }


    public function isRejectedDueBlacklistValidation(): bool
    {
        $pattern = '/^(800\.[32])/';
        return preg_match($pattern, $this->getCode());
    }


    public function isRejectedDueRiskValidation(): bool
    {
        $pattern = '/^(800\.1[123456]0)/';
        return preg_match($pattern, $this->getCode());
    }


    public function isRejectedDueConfigurationValidation(): bool
    {
        $pattern = '/^(600\.[23]|500\.[12]|800\.121)/';
        return preg_match($pattern, $this->getCode());
    }


    public function isRejectedDueRegistrationValidation(): bool
    {
        $pattern = '/^(100\.[13]50)/';
        return preg_match($pattern, $this->getCode());
    }


    public function isRejectedDueJobValidation(): bool
    {
        $pattern = '/^(100\.250|100\.360)/';
        return preg_match($pattern, $this->getCode());
    }


    public function isRejectedDueReferenceValidation(): bool
    {
        $pattern = '/^(700\.[1345][05]0)/';
        return preg_match($pattern, $this->getCode());
    }


    public function isRejectedDueFormatValidation(): bool
    {
        $pattern = '/^(200\.[123]|100\.[53][07]|800\.900|100\.[69]00\.500)/';
        return preg_match($pattern, $this->getCode());
    }


    public function isRejectedDueContactValidation(): bool
    {
        $pattern = '/^(100\.700|100\.900\.[123467890][00-99])/';
        return preg_match($pattern, $this->getCode());
    }


    public function isRejectedDueAccountValidation(): bool
    {
        $pattern = '/^(100\.100|100.2[01])/';
        return preg_match($pattern, $this->getCode());
    }


    public function isRejectedDueAmountValidation(): bool
    {
        $pattern = '/^(100\.55)/';
        return preg_match($pattern, $this->getCode());
    }


    public function isRejectedDueRiskManagement(): bool
    {
        $pattern = '/^(100\.380\.[23]|100\.380\.101)/';
        return preg_match($pattern, $this->getCode());
    }


    private function isChargebackRelated(): bool
    {
        $pattern = '/^(000\.100\.2)/';
        return preg_match($pattern, $this->getCode());
    }


}