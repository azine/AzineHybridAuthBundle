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
	public function __construct(AzineHybridAuth $hybridAuth, Session $session, $providers){
		$this->hybridAuth = $hybridAuth;
		$this->contacts = array(); //$session->get(self::CONTACTS_SESSION_NAME, array());
		$this->loadedProviders = array(); //$session->get(self::LOADED_PROVIDERS_NAME, array());
		$this->providers = array_keys($providers);
		$this->session = $session;
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
			if(!array_key_exists($provider, $this->loadedProviders) || sizeof($this->loadedProviders[$provider]) == 0){
				$newContacts = $this->getUserContactsFor($provider);
				$this->loadedProviders[$provider] = $newContacts;
				$this->session->set(self::LOADED_PROVIDERS_NAME, $this->loadedProviders);
				$this->session->save();
			}
		}

		// merge and sort the old and new contacts
		foreach ($this->loadedProviders as $provider => $contacts){
			$this->contacts = $this->mergeContacts($this->contacts, $contacts);
		}

		$this->contacts = $this->sortContacts($this->contacts);

		$this->session->set(self::CONTACTS_SESSION_NAME, $this->contacts);
		$this->session->save();
	}

	/**
	 * In this default implementation, all the contacts are just shared in one list, no
	 * merging of duplicates is done.
	 *
	 * You can override this with your own logic as required.
	 *
	 * @param array of UserContact $xingContacts
	 * @param array of UserContact $linkedinContacts
	 * @return array of UserContact
	 */
	protected function mergeContacts($xingContacts, $linkedinContacts){
		return array_merge($xingContacts, $linkedinContacts);
	}

	/**
	 * In this default implementation, all the contacts are sorted in
	 * alphabetic order by their lastName.
	 *
	 * Comparison is done in the private static function firstLastNameContactSorter
	 *
	 * You can override both functions with your own logic as required.
	 *
	 * @param array of UserContact unsorted contacts
	 * @return array of UserContact sorted contacts
	 */
	protected function sortContacts($contacts){
		usort($contacts, array($this, 'firstLastNameContactSorter'));
		return $contacts;
	}

	private static function firstLastNameContactSorter(UserContact $a, UserContact $b) {
		return strnatcasecmp($a->lastName, $b->lastName);;
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

		$contacts = $this->hybridAuth->getProvider($provider)->getUserContacts();
		$userContacts = array();
		foreach ($contacts as $next){
			$userContacts[] = new UserContact("", "", $next);
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
				$oResponse = $api->get("users/me/contacts?limit=$fetchSize&user_fields=id,display_name,permalink,web_profiles,photo_urls,first_name,last_name,interests,active_email&offset=$fetchOffset");
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
		$aContacts = array();
		foreach($users as $aTitle) {
			$oContact = new UserContact();
			$oContact->identifier	= (property_exists($aTitle, 'id'))          	? $aTitle->id           : '';
			$oContact->profileURL	= (property_exists($aTitle, 'permalink'))   	? $aTitle->permalink    : '';
			$oContact->firstName 	= (property_exists($aTitle, 'first_name'))		? $aTitle->first_name 	: '';
			$oContact->lastName		= (property_exists($aTitle, 'last_name')) 		? $aTitle->last_name 	: '';
			$oContact->displayName	= $oContact->firstName." ".$oContact->lastName;
			$oContact->description	= (property_exists($aTitle, 'interests'))   	? $aTitle->interests    : '';
			$oContact->email		= (property_exists($aTitle, 'active_email'))	? $aTitle->active_email : '';

			// My own priority: Homepage, blog, other, something else.
			if (property_exists($aTitle, 'web_profiles')) {
				$oContact->webSiteURL = (property_exists($aTitle->web_profiles, 'homepage')) ? $aTitle->web_profiles->homepage[0] : null;
				if (null === $oContact->webSiteURL) {
					$oContact->webSiteURL = (property_exists($aTitle->web_profiles, 'blog')) ? $aTitle->web_profiles->blog[0] : null;
				}
				if (null === $oContact->webSiteURL) {
					$oContact->webSiteURL = (property_exists($aTitle->web_profiles, 'other')) ? $aTitle->web_profiles->other[0] : null;
				}
				// Just use *anything*!
				if (null === $oContact->webSiteURL) {
					foreach ($aTitle->web_profiles as $aUrl) {
						$oContact->webSiteURL = $aUrl[0];
						break;
					}
				}
			}

			// We use the largest picture available.
			if (property_exists($aTitle, 'photo_urls') && property_exists($aTitle->photo_urls, 'large')) {
				$oContact->photoURL = (property_exists($aTitle->photo_urls, 'large')) ? $aTitle->photo_urls->large : '';
			}

			$aContacts[] = $oContact;
		}

		return $aContacts;
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


		$contacts = ARRAY();

		foreach( $users as $connection ) {
			$uc = new UserContact();

			$uc->identifier  = (string) $connection->id;
			$uc->firstName = (string) $connection->{'first-name'};
			$uc->lastName = (string) $connection->{'last-name'};
			$uc->displayName = (string) $connection->{'first-name'} . " " . $connection->{'last-name'};
			$uc->profileURL  = (string) $connection->{'public-profile-url'};
			$uc->photoURL    = (string) $connection->{'picture-url'};
			$uc->description = (string) $connection->{'summary'};

			$contacts[] = $uc;
		}

		return $contacts;
	}


}