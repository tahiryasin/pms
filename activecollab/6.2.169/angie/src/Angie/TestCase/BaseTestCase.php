<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

namespace Angie\TestCase;

use DateValue;
use PHPUnit\Framework\TestCase;

abstract class BaseTestCase extends TestCase
{
    protected $current_timestamp;

    protected function setUp()
    {
        parent::setUp();

        $this->current_timestamp = DateValue::lockCurrentTimestamp();
    }

    protected function tearDown()
    {
        DateValue::unlockCurrentTimestamp();

        parent::tearDown();
    }

    /**
     * SimpleTest compatible pass() method that increases number of assertions.
     *
     * @param string $message
     */
    public function pass($message = '')
    {
        $this->assertTrue(true, $message);
    }

    /**
     * Asserts that two associative arrays are similar.
     *
     * Both arrays must have the same indexes with identical values
     * without respect to key ordering
     *
     * @param array $expected
     * @param array $array
     */
    public function assertArraySimilar(array $expected, array $array)
    {
        $this->assertTrue(count(array_diff_key($array, $expected)) === 0);

        foreach ($expected as $key => $value) {
            if (is_array($value)) {
                $this->assertArraySimilar($value, $array[$key]);
            } else {
                $this->assertContains($value, $array);
            }
        }
    }
}
