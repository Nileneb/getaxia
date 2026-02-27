<?php

// This file is used by Intelephense/PHPStan to resolve $this inside Pest test closures.
// Pest binds $this to Tests\TestCase in Feature tests, but static analysis tools cannot detect this.
// See: https://pestphp.com/docs/ide-support

/** @link https://pestphp.com/docs/configuring-tests */
uses(Tests\TestCase::class)->in('Feature');
