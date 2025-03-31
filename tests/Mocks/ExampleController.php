<?php

namespace Exolnet\Translation\Tests\Mocks;

class ExampleController
{
    /**
     * @return string
     */
    public function index(): string
    {
        return 'index';
    }

    /**
     * @param string $id
     * @return string
     */
    public function show(string $id): string
    {
        return 'show#' . $id;
    }
}
