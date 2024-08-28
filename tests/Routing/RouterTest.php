<?php

use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    /**
     * Tests whether the `Router` correctly matches routes and calls the
     * correct handler. Routes should be in order of specificity and whether or
     * not the route is static or dynamic, it's parameters are required or
     * optional and whether the route is traditional or file-based.
     */
    public function testCorrectHandlerCalledForRoute(): void
    {
        //
    }
}
