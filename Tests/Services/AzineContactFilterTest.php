<?php
namespace Azine\HybridAuthBundle\Services;

use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class AzineContactFilterTest extends TestCase {

    public function testFilter(){
        $filter = new AzineContactFilter();
        $testData = array("a", "b", "c");
        $this->assertEquals($testData, $filter->filter($testData, $testData));
    }
}