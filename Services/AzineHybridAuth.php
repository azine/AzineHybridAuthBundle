<?php
namespace Azine\HybridAuthBundle\Services;

use Azine\HybridAuthBundle\DependencyInjection\AzineHybridAuthExtension;

use Azine\HybridAuthBundle\Entity\HybridAuthSessionData;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AzineHybridAuth {
	/**
	 * ID of the sessionDataCookie
	 */
	const cookieName = "azine_hybridauth_session";

	/**
	 * @var ObjectManager
	 */
	private $objectManager;

	/**
	 * @var UserInterface
	 */
	private $currentUser;

	/**
	 * @var bool
	 */
	private $storeForUser;

	/**
	 * @var bool
	 */
	private $storeAsCookie;

	/**
	 * @var int
	 */
	private $expiresInDays;

	/**
	 * Configured Instances of HybridAuth
	 * @var array or HybridAuth
	 */
	private $instances = array();

	/**
	 * HybridAuth configuration
	 * @var array
	 */
	private $config;

	/**
	 *
	 * @param UrlGeneratorInterface $router
	 * @param UserInterface $user
	 * @param TokenStorageInterface $tokenStorage
	 * @param ObjectManager $manager
	 * @param array $config
	 * @param bool $storeForUser
	 * @param $storeAsCookie
	 * @param $expiresInDays
	 */
	public function __construct(UrlGeneratorInterface $router, UserInterface $user, ObjectManager $manager, $config, $storeForUser, $storeAsCookie, $expiresInDays){
		$base_url = $router->generate($config[AzineHybridAuthExtension::ENDPOINT_ROUTE], array(), UrlGeneratorInterface::ABSOLUTE_URL);
		$config[AzineHybridAuthExtension::BASE_URL] = $base_url;
		$this->config = $config;
		$this->objectManager = $manager;
		$this->storeForUser = $storeForUser;
		$this->storeAsCookie = $storeAsCookie;
		$this->currentUser = $user;
		$this->expiresInDays = $expiresInDays;
	}


	/**
	 * Get a Hybrid_Auth instance initialised for the given provider.
	 * HybridAuthSessions will be restored from DB and/or cookies, according to the bundle configuration.
	 *
	 * @param $cookieSessionData
	 * @param $provider
	 * @return \Hybrid_Auth
	 */
	public function getInstance($cookieSessionData, $provider){
		if(array_key_exists($provider, $this->instances)){
			$hybridAuth = $this->instances[$provider];
		} else {
			$hybridAuth = new \Hybrid_Auth($this->config);
			$this->instances[$provider] = $hybridAuth;
		}
		$restoredFromDB = false;
		$sessionData = null;
		$isExpiredSession = false;

		$result = $this->objectManager->getRepository("AzineHybridAuthBundle:HybridAuthSessionData")->findOneBy(array('username' => $this->currentUser->getUsername(), 'provider' => $provider));

		if($result instanceof HybridAuthSessionData){

			$isExpiredSession =  $this->isExpiredSession($result);
		}

		if($isExpiredSession){

			$this->deleteSession($provider);
		}

		if(!$isExpiredSession && $this->storeForUser && $this->currentUser instanceof UserInterface){
			// try from database
			if($result){
				$sessionData = $result->getSessionData();
				$restoredFromDB = true;
			}
		}
		if($sessionData === null && $cookieSessionData !== null) {
			// try from cookie
			$sessionData = gzinflate($cookieSessionData);

			// user is looged in but auth session is not yet stored in db => store now
			if(!$restoredFromDB){
				$this->saveAuthSessionData($sessionData, $provider);
			}
		}
		if($sessionData) {
			$hybridAuth->restoreSessionData($sessionData);
		}

		return $hybridAuth;
	}

	/**
	 * @param Request $request
	 * @param $provider
	 * @param $sessionData
	 * @return Cookie | null
	 */
	public function storeHybridAuthSessionData(Request $request, $provider, $sessionData){
		$this->saveAuthSessionData($sessionData, $provider);

		if($this->storeAsCookie){
			return new Cookie($this->getCookieName($provider), gzdeflate($sessionData), new \DateTime($this->expiresInDays .' days'), '/', $request->getHost(), $request->isSecure(), true);
		}
		return null;
	}

	/**
	 * Delete the HybridAuthSessionData entity from the database
	 * @param $provider
	 */
	public function deleteSession($provider){
		if($this->currentUser instanceof UserInterface) {
			$result = $this->objectManager->getRepository("AzineHybridAuthBundle:HybridAuthSessionData")->findOneBy(array('username' => $this->currentUser->getUsername(), 'provider' => $provider));
			if ($result) {
				$this->objectManager->remove($result);
				$this->objectManager->flush();
			}
		}
	}

	/**
	 * Save as HybridAuthSessionData entity to the database.
	 * Checks the bundle configuration before saving.
	 * @param $sessionData
	 * @param $provider
	 */
	private function saveAuthSessionData($sessionData, $provider){
		if($this->storeForUser && $this->currentUser instanceof UserInterface) {
			$hybridAuthData = $this->objectManager->getRepository("AzineHybridAuthBundle:HybridAuthSessionData")->findOneBy(array('username' => $this->currentUser->getUsername(), 'provider' => strtolower($provider)));
			if (!$hybridAuthData) {
				$hybridAuthData = new HybridAuthSessionData();
				$hybridAuthData->setUserName($this->currentUser->getUsername());
				$hybridAuthData->setProvider(strtolower($provider));

				$expirationDate = new \DateTime();
				$expirationDate->modify('+ '. $this->expiresInDays .' day');

				$hybridAuthData->setExpiresAt($expirationDate);
				$this->objectManager->persist($hybridAuthData);
			}
			$hybridAuthData->setSessionData($sessionData);
			$this->objectManager->flush();
		}
	}

	public function getCookieName($provider){
		return self::cookieName."_".strtolower($provider);
	}

	/**
	 * Use this function to get access to a HybridAuthProvider.
	 *
	 * Calling this method will log the user in (make a roundtrip to the providers site and back to your site again)
	 * and call the page again that you came from.
	 *
	 * When logged (allready) it will return the hybridAuth provider.
	 *
	 * @param $authSessionData
	 * @param string $provider_id
	 * @param boolean $require_login
	 * @return \Hybrid_Provider_Model
	 */
	public function getProvider($authSessionData, $provider_id, $require_login = true){
		$adapter = $this->getInstance($authSessionData, $provider_id)->getAdapter($provider_id);
		if($require_login && !$adapter->isUserConnected()){
			$adapter->login();
		}
		return $adapter;
	}

	/**
	 * Check if the current user has allowed access to the given provider
	 * @param Request $request
	 * @param string $provider_id
	 * @return bool true if access to the provider is granted for this app.
	 */
	public function isConnected(Request $request, $provider_id){
        $sessionData = $request->cookies->get($this->getCookieName($provider_id));
		$adapter = $this->getInstance($sessionData, $provider_id)->getAdapter($provider_id);
		$connected = $adapter->isUserConnected();
		return $connected;
	}
	
	/**
     * Get the Xing Adapter
     * @return \Hybrid_Providers_XING
     */
	public function getXing(){
		return $this->getProvider(null, "xing");
	}

	/**
	 * Get the Xing api (OAuthClient)
	 *
	 * @return \OAuth1Client
	 */
	public function getXingApi(){
		return $this->getXing()->api();
	}

	/**
	 * Get the LinkedIn Adapter
	 *
	 * @return \Hybrid_Providers_LinkedIn
	 */
	public function getLinkedIn(){
		return $this->getProvider(null, "linkedin");
	}

    /**
     * Get the LinkedIn api (LinkedIn PHP-client)
     *
     * @return \LinkedIn
     */
	public function getLinkedInApi(){
		return $this->getLinkedIn()->api();
	}

	/**
	 * Get if auth token is expired
	 * @param HybridAuthSessionData $data
	 *
	 * @return boolean
	 */
	public function isExpiredSession(HybridAuthSessionData $data)
	{
		if($data->getExpiresAt() <  new \DateTime()){

			return true;
		}

		return false;
	}

}