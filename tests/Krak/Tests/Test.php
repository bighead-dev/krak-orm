<?php

namespace Krak\Tests;

/**
 * Simple Testing Interface
 */
interface Test
{
    /**
     * Main entry point for a test.
     * @param array $argv
     */
    public function main($argv);
}
