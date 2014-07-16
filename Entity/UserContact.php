<?php
namespace Azine\HybridAuthBundle\Entity;


class UserContact extends \Hybrid_User_Contact{

	public $firstName = null;

	public $lastName = null;

	public function __construct($firstName = null, $lastName = null, \Hybrid_User_Contact $contact = null){
		$this->firstName = $firstName;
		$this->lastName = $lastName;
		if($contact){
		    $this->identifier = $contact->identifier;
			$this->webSiteURL = $contact->webSiteURL;
			$this->profileURL = $contact->profileURL;
			$this->photoURL = $contact->photoURL;
			$this->displayName = $contact->displayName;
			$this->description = $contact->description;
			$this->email = $contact->email;
		}
	}

}

