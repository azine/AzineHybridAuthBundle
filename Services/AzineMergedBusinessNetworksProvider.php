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


	public function __construct(AzineHybridAuth $hybridAuth, Session $session){
		$this->hybridAuth = $hybridAuth;
		$this->contacts = $session->get("business_contacts", array());
		$this->session = $session;
	}

	/**
	 * Get user-profiles from xing and linked-in
	 * @param int $pageSize
	 * @param int $offest
	 */
	public function getContactProfiles($pageSize = 50, $offset = 0){
		// check if we need to load more contacts
		if(sizeof($this->contacts) == 0){
			$this->getAllContacts();
		}

		// return one page
		return array_slice($this->contacts, $offset, $pageSize, true);
	}

	/**
	 * Fetch more contacts from xing and linkedin.
	 * @param int $pageSize
	 * @param int $offset
	 */
	private function getAllContacts(){
		$nextXi = $this->getXingContacts();
		$nextLi = $this->getLinkedInContacts();

		// merge and sort the old and new contacts
		$this->contacts = array_merge($this->contacts, $nextLi, $nextXi);
		usort($this->contacts, array($this, 'businessContactSorter'));

		$this->session->set("business_contacts", $this->contacts);
		$this->session->save();
	}

	private static function businessContactSorter(UserContact $a, UserContact $b) {
		return strnatcasecmp($a->lastName, $b->lastName);;
	}


	public function getXingContacts(){
		$api = $this->hybridAuth->getXingApi();
		$fetchSize = 100;
		$fetchOffset = 0;
		$fetchMore = true;
		$users = array();
		try {
			while ($fetchMore){
				$oResponse = $api->get("users/me/contacts?limit=$fetchSize&user_fields=id,display_name,permalink,web_profiles,photo_urls,first_name,last_name,interests,active_email&offset=$fetchOffset");
				$users = array_merge($users, $oResponse->contacts->users);
				$fetchOffset = $fetchOffset + $fetchSize;
				$fetchMore = $fetchSize == sizeof($oResponse->contacts->users);
			}
		}
		catch(Exception $e) {
			throw new Exception('Could not fetch contacts. ' . $this->providerId . ' returned an error: ' . $e . '.');
		}


		// Create the contacts array.
		$aContacts = array();
		foreach($users as $aTitle) {
			$oContact = new UserContact();
			$oContact->identifier	= (property_exists($aTitle, 'id'))          	? $aTitle->id           : '';
			$oContact->profileURL	= (property_exists($aTitle, 'permalink'))   	? $aTitle->permalink    : '';
			$oContact->firstName 	= (property_exists($aTitle, 'first_name'))		? $aTitle->first_name : '';
			$oContact->lastName		= (property_exists($aTitle, 'last_name')) 		? $aTitle->last_name : '';
			$oContact->displayName	= (property_exists($aTitle, 'display_name'))	? $aTitle->display_name : '';
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
			throw new Exception( "User contacts request failed! {$this->providerId} returned an error: $e" );
		}


		$contacts = ARRAY();

		foreach( $users as $connection ) {
			$uc = new UserContact();

			$uc->identifier  = (string) $connection->id;
			$uc->firstName = (string) $connection->{'first-name'};
			$uc->lastName = (string) $connection->{'last-name'};
			$uc->displayName = (string) $connection->{'last-name'} . " " . $connection->{'first-name'};
			$uc->profileURL  = (string) $connection->{'public-profile-url'};
			$uc->photoURL    = (string) $connection->{'picture-url'};
			$uc->description = (string) $connection->{'summary'};

			$contacts[] = $uc;
		}

		return $contacts;
	}


}