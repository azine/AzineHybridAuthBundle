<?php
namespace Azine\HybridAuthBundle\Services;

use Azine\HybridAuthBundle\Entity\UserContact;

use Symfony\Component\HttpFoundation\Session\Session;

use Azine\HybridAuthBundle\DependencyInjection\AzineHybridAuthExtension;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

interface ContactMerger {

	/**
	 * @param array of UserContact already loaded/merged contacts
	 * @param array of array( providerId => array of UserContacts)
	 * @return array of UserContact
	 */
	public function merge(array $allContacts, array $newContacts);	
}