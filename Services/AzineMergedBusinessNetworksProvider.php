<?php
namespace Azine\HybridAuthBundle\Services;

use Azine\HybridAuthBundle\Entity\UserContact;

use Symfony\Component\HttpFoundation\Session\Session;

use Azine\HybridAuthBundle\DependencyInjection\AzineHybridAuthExtension;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AzineMergedBusinessNetworksProvider {

	/**
	 * @var AzineHybridAuth
	 */
	private $hybridAuth;

	/**
	 * @var array
	 */
	private $contacts;

	/**
	 * @var Session
	 */
	private $session;

	/**
	 * @var array of provider ids
	 */
	private $providers;

	/**
	 * @var array of provider ids that are loaded already
	 */
	private $loadedProviders;
	
	/**
	 * @var ContactSorter
	 */
	private $sorter;
	
	/**
	 * @var ContactMerger
	 */
	private $merger;

	/**
	 * @var GenderGuesser
	 */
	private $genderGuesser;

	/**
	 * @var string
	 */
	const CONTACTS_SESSION_NAME = "hybrid_auth_contacts";
	const LOADED_PROVIDERS_NAME = "hybrid_auth_loaded_providers";

	/**
	 * Get the contacts from all configured providers
	 * @param AzineHybridAuth $hybridAuth
	 * @param Session $session
	 * @param array $providers
	 */
	public function __construct(AzineHybridAuth $hybridAuth, Session $session, ContactSorter $sorter, ContactMerger $merger, GenderGuesser $genderGuesser, array $providers){
		$this->hybridAuth = $hybridAuth;
		$this->sorter = $sorter;
		$this->merger = $merger;
		$this->contacts = $session->get(self::CONTACTS_SESSION_NAME, array());
		$this->loadedProviders = $session->get(self::LOADED_PROVIDERS_NAME, array());
		$this->providers = array_keys($providers);
		$this->session = $session;
		$this->genderGuesser = $genderGuesser;
	}

	/**
	 * Get user-profiles from xing and linked-in
	 * @param int $pageSize
	 * @param int $offest
	 */
	public function getContactProfiles($pageSize = 50, $offset = 0, $tryToConnect = false){
		// check if the contacts are loaded already
		if(sizeof($this->providers) != sizeof($this->loadedProviders)){
			$this->getAllContacts();
		}

		// return one page
		return array_slice($this->contacts, $offset, $pageSize, true);
	}

	/**
	 * Fetch all contacts from the networks
	 */
	private function getAllContacts(){
		foreach ($this->providers as $provider){
			$connected = $this->hybridAuth->getProvider($provider, false)->isUserConnected();
			if($connected && (!array_key_exists($provider, $this->loadedProviders) || sizeof($this->loadedProviders[$provider]) == 0)){
				$newContacts = $this->getUserContactsFor($provider);
				$this->loadedProviders[$provider] = $newContacts;
				$this->session->set(self::LOADED_PROVIDERS_NAME, $this->loadedProviders);
				$this->session->save();
			}
		}

		// merge the old and new contacts
		$this->contacts = $this->merger->merge($this->loadedProviders);

		// sort all contacts
		usort($this->contacts, array($this->sorter, 'compare'));

		$this->session->set(self::CONTACTS_SESSION_NAME, $this->contacts);
		$this->session->save();
	}

	/**
	 * Get ALL xing contacts of the current user
	 * @throws Exception
	 * @return multitype:\Azine\HybridAuthBundle\Entity\UserContact
	 */
	public function getUserContactsFor($provider){
		if($provider == "Xing"){
			return $this->getXingContacts();
		} elseif ($provider == "LinkedIn"){
			return $this->getLinkedInContacts();
		}

		$userContacts = array();
		foreach ($this->hybridAuth->getProvider($provider)->getUserContacts() as $next){
			$nextContact = new UserContact($provider);
			$nextContact->identifier	= $next->identifier;
			$nextContact->profileURL	= $next->profileURL;
			$nextContact->firstName 	= $next->firstName;
			$nextContact->lastName		= $next->lastName;
			$nextContact->displayName	= $nextContact->firstName." ".$nextContact->lastName;
			$nextContact->description	= $next->description;
			$nextContact->email			= $next->email;
		}
		return $usersContacts;
	}

	/**
	 * Get ALL xing contacts of the current user
	 * @throws Exception
	 * @return multitype:\Azine\HybridAuthBundle\Entity\UserContact
	 */
	public function getXingContacts(){
		$api = $this->hybridAuth->getXingApi();
		$fetchSize = 100;
		$fetchOffset = 0;
		$fetchMore = true;
		$users = array();
		try {
			while ($fetchMore){
				$oResponse = $api->get("users/me/contacts?limit=$fetchSize&user_fields=id,display_name,permalink,web_profiles,photo_urls,first_name,last_name,interests,gender,active_email&offset=$fetchOffset");
				if(isset($oResponse->error_name)){
					throw new \Exception($oResponse->error_name." : ".$oResponse->message);
				}
				$users = array_merge($users, $oResponse->contacts->users);
				$fetchOffset = $fetchOffset + $fetchSize;
				$fetchMore = $fetchSize == sizeof($oResponse->contacts->users);
			}
		}
		catch(\Exception $e) {
			throw new \Exception('Could not fetch contacts. Xing returned an error.', $e->getCode(), $e);
		}


		// Create the contacts array.
		$xingContacts = array();
		foreach($users as $aTitle) {
			$nextContact = new UserContact("Xing");
			$nextContact->identifier	= (property_exists($aTitle, 'id'))          	? $aTitle->id           : '';
			$nextContact->profileURL	= (property_exists($aTitle, 'permalink'))   	? $aTitle->permalink    : '';
			$nextContact->firstName 	= (property_exists($aTitle, 'first_name'))		? $aTitle->first_name 	: '';
			$nextContact->lastName		= (property_exists($aTitle, 'last_name')) 		? $aTitle->last_name 	: '';
			$nextContact->displayName	= $nextContact->firstName." ".$nextContact->lastName;
			$nextContact->description	= (property_exists($aTitle, 'interests'))   	? $aTitle->interests    : '';
			$nextContact->email			= (property_exists($aTitle, 'active_email'))	? $aTitle->active_email : '';
			$nextContact->gender		= (property_exists($aTitle, 'gender'))			? $aTitle->gender : '';
				

			// My own priority: Homepage, blog, other, something else.
			if (property_exists($aTitle, 'web_profiles')) {
				$nextContact->webSiteURL = (property_exists($aTitle->web_profiles, 'homepage')) ? $aTitle->web_profiles->homepage[0] : null;
				if (null === $nextContact->webSiteURL) {
					$nextContact->webSiteURL = (property_exists($aTitle->web_profiles, 'blog')) ? $aTitle->web_profiles->blog[0] : null;
				}
				if (null === $nextContact->webSiteURL) {
					$nextContact->webSiteURL = (property_exists($aTitle->web_profiles, 'other')) ? $aTitle->web_profiles->other[0] : null;
				}
				// Just use *anything*!
				if (null === $nextContact->webSiteURL) {
					foreach ($aTitle->web_profiles as $aUrl) {
						$nextContact->webSiteURL = $aUrl[0];
						break;
					}
				}
			}

			// We use the largest picture available.
			if (property_exists($aTitle, 'photo_urls') && property_exists($aTitle->photo_urls, 'large')) {
				$nextContact->photoURL = (property_exists($aTitle->photo_urls, 'large')) ? $aTitle->photo_urls->large : '';
			}

			$xingContacts[] = $nextContact;
		}

		return $xingContacts;
	}

	/**
	 * Get ALL linkedin contacts of the current user
	 * @throws Exception
	 * @return multitype:\Azine\HybridAuthBundle\Entity\UserContact
	 */
	public function getLinkedInContacts(){
		$api = $this->hybridAuth->getLinkedInApi();
		$fetchSize = 500;
		$fetchMore = true;
		$fetchOffset = 0;
		$users = array();

		try{
			while ($fetchMore){
				$response = $api->profile("~/connections:(id,first-name,last-name,picture-url,public-profile-url,summary)?start=$fetchOffset&count=$fetchSize");
				$connectionsXml = new \SimpleXMLElement( $response['linkedin'] );
				foreach ($connectionsXml->person as $person){
					$users[] = $person;
				}
				$fetchMore = $fetchSize == sizeof($connectionsXml->person);
				$fetchOffset = $fetchOffset + $fetchSize;
			}
		}
		catch( \LinkedInException $e ){
			throw new Exception( "User contacts request failed! {$this->providerId} returned an error.", $e->getCode(), $e );
		}


		$contacts = array();

		foreach( $users as $connection ) {
			$uc = new UserContact("LinkedIn");

			$uc->identifier  = (string) $connection->id;
			$uc->firstName = (string) $connection->{'first-name'};
			$uc->lastName = (string) $connection->{'last-name'};
			$uc->displayName = (string) $connection->{'first-name'} . " " . $connection->{'last-name'};
			$uc->profileURL  = (string) $connection->{'site-standard-profile-request'};
			$uc->photoURL    = (string) $connection->{'picture-url'};
			$uc->description = (string) $connection->{'summary'};
			$uc->gender 	 = $this->genderGuesser->guess($uc->firstName);

			$contacts[] = $uc;
		}

		return $contacts;
	}
	
	/**
	 * Get the basic profile of the current users contact with the given user id.
	 * @param string $provider
	 * @param string $contactId
	 * @return UserContact
	 */
	public function getUserContactBasicProfile($provider, $contactId){
		if(!array_key_exists($provider, $this->loadedProviders)){
			$this->loadedProviders[$provider] = $this->getUserContactsFor($provider);
			$this->session->set(self::LOADED_PROVIDERS_NAME, $this->loadedProviders);
			$this->session->save();
		}
		
		foreach ($this->loadedProviders[$provider] as $userContact){
			if($userContact->identifier == $contactId){
				return $userContact;
			}
		}
		return null;
	}
}