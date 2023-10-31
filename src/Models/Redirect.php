<?php

namespace TimoPaul\ProcessingPartners\Models;

use stdClass;

class Redirect
{
    private $url = null;
    private $parameters = [];
    private $preconditions = [];

    /**
     * Creates a new `Redirect` for the `Response`.
     *
     * @param stdClass $redirect
     */
    public function __construct(stdClass $redirect)
    {
        if (property_exists($redirect, 'url')) {
            $this->url = $redirect->url;
        }

        if (property_exists($redirect, 'parameters') && is_array($redirect->parameters)) {
            $this->parameters = $redirect->parameters;
        }

        if (property_exists($redirect, 'preconditions') && is_array($redirect->preconditions)) {
            $this->preconditions = $redirect->preconditions;
        }
    }

    /**
     * Returns the URL for the redirect.
     *
     * @return string|null
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Returns `true` if there are parameters for the redirection.
     *
     * @return bool
     */
    public function hasParameters(): bool
    {
        return 0 < count($this->getParameters());
    }

    /**
     * Returns the parameters for the redirect.
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Returns `true` if there are parameters for the redirection.
     *
     * @return bool
     */
    public function hasPreconditions(): bool
    {
        return 0 < count($this->getPreconditions());
    }

    /**
     * Returns the parameters for the redirect.
     *
     * @return array
     */
    public function getPreconditions(): array
    {
        return $this->preconditions;
    }



}