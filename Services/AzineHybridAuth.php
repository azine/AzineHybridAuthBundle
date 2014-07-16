<?php
namespace Azine\HybridAuthBundle\Services;

use Azine\HybridAuthBundle\DependencyInjection\AzineHybridAuthExtension;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AzineHybridAuth {

	/**
	 * @var \Hybrid_Auth
	 */
	private $hybridAuth;

	/**
	 *
	 * @param UrlGeneratorInterface $router
	 * @param array $config
	 */
	public function __construct(UrlGeneratorInterface $router, $config){
		$base_url = $router->generate($config[AzineHybridAuthExtension::ENDPOINT_ROUTE], array(), UrlGeneratorInterface::ABSOLUTE_URL);
		$config[AzineHybridAuthExtension::BASE_URL] = $base_url;
		$this->hybridAuth = new \Hybrid_Auth($config);
	}


	/**
	 * This function is used by the HybridAuthEndPointController.
	 * @return \Hybrid_Auth
	 */
	public function getInstance(){
		return $this->hybridAuth;
	}

	/**
	 * Use this function to get access to a HybridAuthProvider.
	 *
	 * Calling this method will log the user in (make a roundtrip to the providers site and back to your site again)
	 * and call the page again that you came from.
	 *
	 * When logged (allready) it will return the hybridAuth provider.
	 *
	 * @param string $provider_id
	 * @param boolean $require_login
	 * @return \Hybrid_Provider_Model
	 */
	public function getProvider($provider_id, $require_login = true){
		$adapter = $this->hybridAuth->getAdapter($provider_id);
		if($require_login && !$adapter->isUserConnected()){
			$adapter->login();
		}
		return $adapter;
	}

	/**
	 * Check if the current user has allowed access to the given provider
	 * @param string $provider_id
	 * @return boolean true if access to the provider is granted for this app.
	 */
	public function isConnected($provider_id){
		$adapter = $this->hybridAuth->getAdapter($provider_id);
		$connected = $adapter->isUserConnected();
		return $connected;
	}

    /**
     * Get the Xing Adapter
     * @return \Hybrid_Providers_XING
     */
	public function getXing(){
		return $this->getProvider("xing");
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
		return $this->getProvider("linkedin");
	}

    /**
     * Get the LinkedIn api (LinkedIn PHP-client)
     *
     * @return \LinkedIn
     */
	public function getLinkedInApi(){
		return $this->getLinkedIn()->api();
	}
}