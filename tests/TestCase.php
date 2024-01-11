<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $tenancy = false;

    public function setUp(): void
    {
        parent::setUp();

        if ($this->tenancy) {
            // do something
            //$this->initializeTenancy();
        }
    }
}
