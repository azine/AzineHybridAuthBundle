<?php
namespace Azine\HybridAuthBundle\Services;

use Azine\HybridAuthBundle\Entity\UserContact;

use Symfony\Component\HttpFoundation\Session\Session;

use Azine\HybridAuthBundle\DependencyInjection\AzineHybridAuthExtension;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

interface ContactFilter {

	/**
	 * @param array of UserContacts
     * @param array of FilterOptions
	 * @return array of UserContact
	 */
	public function filter(array $contacts, array $filterParams);
}