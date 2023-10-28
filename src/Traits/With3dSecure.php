<?php

namespace TimoPaul\ProcessingPartners\Traits;

use TimoPaul\ProcessingPartners\Exceptions\InvalidParameterException;
use TimoPaul\ProcessingPartners\Exceptions\MissingPropertyException;
use TimoPaul\ProcessingPartners\Utils\ThreeDSecureRequestParameter;

trait With3dSecure
{
    use HasParameters;

    private bool $with3dSecure = false;


    /**
     * @throws MissingPropertyException
     * @throws InvalidParameterException
     */
    public function with3dSecure(array $parameter = []): self
    {
        $this->with3dSecure = true;

        foreach ($parameter as $name => $value) {
            $this->addParameter($name, $value);
        }

        $this->init3dSecureRequestParameters();
        return $this;
    }


    public function is3dSecureEnabled(): bool
    {
        return $this->with3dSecure;
    }


    /**
     * Initializes all request parameters for 3D Secure
     * The following parameters must be passed to the request for successful use of 3D Secure:
     * - ThreeDSecureRequestParameter::CUSTOMER_BROWSER_SCREEN_HEIGHT
     * - ThreeDSecureRequestParameter::CUSTOMER_BROWSER_SCREEN_WIDTH
     * - ThreeDSecureRequestParameter::CUSTOMER_BROWSER_SCREEN_COLOR_DEPTH
     *
     * @throws MissingPropertyException
     * @throws InvalidParameterException
     */
    private function init3dSecureRequestParameters(): self
    {
        $acceptHeader = 'text/html';
        $ip = $_SERVER['REMOTE_ADDR'];
        $language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        $browser = get_browser(null, true);
        $javaEnabled = (bool) $browser['javaapplets'] ? 'true' : 'false';
        $javascriptEnabled = (bool) $browser['javascript'] ? 'true' : 'false';

        $this->addParameters([
            ThreeDSecureRequestParameter::CUSTOMER_BROWSER_ACCEPT_HEADER => $acceptHeader,
            ThreeDSecureRequestParameter::CUSTOMER_BROWSER_LANGUAGE => $language,
            ThreeDSecureRequestParameter::CUSTOMER_BROWSER_USER_AGENT => $userAgent,
            ThreeDSecureRequestParameter::CUSTOMER_IP => $ip,
            ThreeDSecureRequestParameter::CUSTOMER_BROWSER_JAVA_ENABLED => $javaEnabled,
            ThreeDSecureRequestParameter::CUSTOMER_BROWSER_JAVASCRIPT_ENABLED => $javascriptEnabled,
            ThreeDSecureRequestParameter::CUSTOMER_BROWSER_CHALLENGE_WINDOW => 5, // Full screen
            ThreeDSecureRequestParameter::CUSTOMER_BROWSER_TIMEZONE => 60,
            // must be passed to the request
            //ThreeDSecureRequestParameter::CUSTOMER_BROWSER_SCREEN_HEIGHT => null,
            //ThreeDSecureRequestParameter::CUSTOMER_BROWSER_SCREEN_WIDTH => null,
            //ThreeDSecureRequestParameter::CUSTOMER_BROWSER_SCREEN_COLOR_DEPTH => null,
        ]);

        return $this;
    }


    /**
     * Returns array of valid request parameters.
     *
     * @return array
     */
    public function getValidParameters(): array
    {
        if ( ! $this->is3dSecureEnabled()) {
            return [];
        }

        return [
            ThreeDSecureRequestParameter::CUSTOMER_BROWSER_ACCEPT_HEADER,
            ThreeDSecureRequestParameter::CUSTOMER_BROWSER_LANGUAGE,
            ThreeDSecureRequestParameter::CUSTOMER_BROWSER_SCREEN_HEIGHT,
            ThreeDSecureRequestParameter::CUSTOMER_BROWSER_SCREEN_WIDTH,
            ThreeDSecureRequestParameter::CUSTOMER_BROWSER_TIMEZONE,
            ThreeDSecureRequestParameter::CUSTOMER_BROWSER_USER_AGENT,
            ThreeDSecureRequestParameter::CUSTOMER_IP,
            ThreeDSecureRequestParameter::CUSTOMER_BROWSER_JAVA_ENABLED,
            ThreeDSecureRequestParameter::CUSTOMER_BROWSER_JAVASCRIPT_ENABLED,
            ThreeDSecureRequestParameter::CUSTOMER_BROWSER_SCREEN_COLOR_DEPTH,
            ThreeDSecureRequestParameter::CUSTOMER_BROWSER_CHALLENGE_WINDOW,
        ];
    }


}