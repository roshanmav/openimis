<?php

namespace Insurance\Openimis\Contracts;

use Illuminate\Contracts\Support\Responsable;

interface OpenImisViewResponse extends Responsable
{
    /**
     * Specify the parameters that should be passed to the view.
     *
     * @param array $parameters
     *
     * @return $this
     */
    public function withParameters($parameters = []);
}
