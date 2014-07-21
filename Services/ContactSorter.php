<?php
namespace Azine\HybridAuthBundle\Services;

use Azine\HybridAuthBundle\Entity\UserContact;

interface ContactSorter {

	/**
	 * @param UserContact $a
	 * @param UserContact $b
	 * @return int 
	 */
	public function compare(UserContact $a, UserContact $b);

}
