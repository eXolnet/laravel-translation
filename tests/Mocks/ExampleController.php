<?php

namespace Exolnet\Translation\Tests\Mocks;

use Illuminate\Routing\Controller;

class ExampleController extends Controller
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
