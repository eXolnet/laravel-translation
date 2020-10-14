<?php

namespace Exolnet\Translation\Blade;

interface BladeExtension
{
    /**
     * Return all the directives
     *
     * @return array
     */
    public function getDirectives();

    /**
     * Return all the cnditionals
     *
     * @return array
     */
    public function getConditionals();
}
