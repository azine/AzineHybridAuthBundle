<?php

namespace Azine\HybridAuthBundle\Tests;

if (class_exists('\PHPUnit\Framework\TestCase')) {
    class AzineMiddleTestCase extends \PHPUnit\Framework\TestCase
    {
    }
} else {
    class AzineMiddleTestCase extends \PHPUnit_Framework_TestCase
    {
    }
}

class AzineTestCase extends AzineMiddleTestCase
{
}
