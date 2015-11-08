<?php
namespace Azine\HybridAuthBundle\Services;

use Azine\HybridAuthBundle\Entity\UserContact;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class AzineContactSorterTest extends TestCase{

	public function testCompare(){
		$u1 = new UserContact("Xing", "m", "firstname", "lasTname");
		$u2 = new UserContact("Xing", "m", "firstname2", "lastname2");
		$u3 = new UserContact("Xing", "m", "firstname3", "Lastname3");

		$sorter = new AzineContactSorter();
		$this->assertEquals(0, $sorter->compare($u1, $u1));
		$this->assertEquals(-1, $sorter->compare($u1, $u2));
		$this->assertEquals(1, $sorter->compare($u3, $u2));
	}
}