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

use stdClass;
use TimoPaul\ProcessingPartners\Exceptions\NoRedirectAvailableException;
use TimoPaul\ProcessingPartners\Models\Redirect;

trait HasRedirect
{
    /**
     * Returns `true` if the response contains a redirect.
     *
     * @return bool
     */
    public function hasRedirect(): bool
    {
        return property_exists($this, 'redirect');
    }

    /**
     * Returns the redirect of the response.
     *
     * @return Redirect
     * @throws NoRedirectAvailableException
     */
    public function getRedirect(): Redirect
    {
        if (property_exists($this, 'redirect')) {
            return new Redirect($this->redirect);
        }

        throw new NoRedirectAvailableException($this);
    }
}