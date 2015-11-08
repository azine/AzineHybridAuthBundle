<?php
namespace Azine\HybridAuthBundle\Services;

class AzineContactMerger implements ContactMerger {

	/**
	 * In this default implementation, all the contacts are just shared in one list, no
	 * merging of duplicates is done.
	 *
	 * @param array of array( providerId => array of UserContacts)
	 * @return array of UserContact
	 */
	public function merge(array $newContacts){
		$contacts = array();
		foreach ($newContacts as $nextProvider => $nextContacts){
			$contacts = array_merge($contacts, $nextContacts);
		}
		return $contacts;
	}
}