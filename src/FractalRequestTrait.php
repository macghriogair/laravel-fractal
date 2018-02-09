<?php

namespace Macgriog\Fractal;

/**
 * Helper to inspect request in a Fractal aware Laravel Controller.
 */
trait FractalRequestTrait
{
    /** @var int */
    protected $defaultPerPage = 100;

    /** @var array */
    protected $allowedIncludes = [];

    /** @var int */
    private $maxPerPage = 1000;

    protected function queryParams()
    {
        return array_diff_key(request()->all(), array_flip(['page']));
    }

    protected function perPage()
    {
        $limit = request()->get('limit', $this->defaultPerPage);
        return ($limit < $this->maxPerPage) ? $limit : $this->maxPerPage;
    }

    protected function eagerLoad()
    {
        return array_intersect($this->allowedIncludes, $this->fractal->getRequestedIncludes());
    }
}
