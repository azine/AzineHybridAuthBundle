<?php

namespace Azine\HybridAuthBundle\Controller;

use Azine\HybridAuthBundle\Services\AzineHybridAuth;
use Symfony\Component\HttpFoundation\ParameterBag;

use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HybridEndPointController extends Controller {

	private $initDone = FALSE;
	/**
	 * @var \Hybrid_Auth
	 */
	private $hybridAuth;

	/**
	 * @var ParameterBag
	 */
	private $requestQuery;

	/**
	 * @param Request $request
	 * @return \Hybrid_Auth
	 */
	private function getHybridAuth(Request $request){
		return $this->getAzineHybridAuth()->getInstance($request);
	}

	/**
	 * @return AzineHybridAuth
	 */
	private function getAzineHybridAuth(){
		return $this->get("azine_hybrid_auth_service");
	}

    /**
     * Process the current request
     *
     * $request - The current request parameters. Leave as NULL to default to use $_REQUEST.
     * @param Request $request
     * @return RedirectResponse|Response
     */
	public function processAction(Request $request) {

		$this->hybridAuth =  $this->getHybridAuth($request);

		// Get the request Vars
		$this->requestQuery = $request->query;

		// If openid_policy requested, we return our policy document
		if ( $this->requestQuery->has('get') && $this->requestQuery->get('get') == "openid_policy" ) {
			return $this->processOpenidPolicy();
		}

		// If openid_xrds requested, we return our XRDS document
		if ( $this->requestQuery->has('get') && $this->requestQuery->get('get') == "openid_xrds" ) {
			return $this->processOpenidXRDS();
		}

		// If we get a hauth.start
		if ( $this->requestQuery->has('hauth_start') && $this->requestQuery->get('hauth_start') ) {
			return $this->processAuthStart();
		}
		// Else if hauth.done
		elseif ( $this->requestQuery->has('hauth_done') && $this->requestQuery->get('hauth_done') ) {
			return $this->processAuthDone();
		}
		// Else we advertise our XRDS document, something supposed to be done from the Realm URL page
		else {
			return $this->processOpenidRealm();
		}
	}

	/**
	 * Process OpenID policy request
	 */
	private function processOpenidPolicy() {
		$output = file_get_contents( dirname(__FILE__) . "/resources/openid_policy.html" );
		return $output;
	}

	/**
	 * Process OpenID XRDS request
	 */
	private function processOpenidXRDS() {
		header("Content-Type: application/xrds+xml");

		$output = str_replace
		(
				"{RETURN_TO_URL}",
				str_replace(
						array("<", ">", "\"", "'", "&"), array("&lt;", "&gt;", "&quot;", "&apos;", "&amp;"),
						$this->hybridAuth->getCurrentUrl( false )
				),
				file_get_contents( dirname(__FILE__) . "/resources/openid_xrds.xml" )
		);
		return new Response($output);
	}

	/**
	 * Process OpenID realm request
	 */
	private function processOpenidRealm()	{

		$output = str_replace
		(
				"{X_XRDS_LOCATION}",
				htmlentities( $this->hybridAuth->getCurrentUrl( false ), ENT_QUOTES, 'UTF-8' ) . "?get=openid_xrds&v=" . \Hybrid_Auth::$version,
				file_get_contents( dirname(__FILE__) . "/resources/openid_realm.html" )
		);
		return new Response($output);
	}

	/**
	 * define:endpoint step 3.
	 */
	private function processAuthStart() {

		$response = $this->authInit();
		if($response instanceof Response){
			return $response;
		}

		$provider_id = trim( strip_tags( $this->requestQuery->get("hauth_start") ) );

		# check if page accessed directly
		if( ! $this->hybridAuth->storage()->get( "hauth_session.$provider_id.hauth_endpoint" ) ) {
			\Hybrid_Logger::error( "Endpoint: hauth_endpoint parameter is not defined on hauth_start, halt login process!" );

			return new Response("You cannot access this page directly.", 404, array(header( "HTTP/1.0 404 Not Found" )));
		}

		# define:hybrid.endpoint.php step 2.
		$hauth = $this->hybridAuth->setup( $provider_id );

		# if REQUESTed hauth_idprovider is wrong, session not created, etc.
		if( ! $hauth ) {
			\Hybrid_Logger::error( "Endpoint: Invalid parameter on hauth_start!" );

			return new Response("Invalid parameter! Please return to the login page and try again.", 404, array(header( "HTTP/1.0 404 Not Found" )));
		}

		try {
			\Hybrid_Logger::info( "Endpoint: call adapter [{$provider_id}] loginBegin()" );

			$hauth->adapter->loginBegin();
		}
		catch ( \Exception $e ) {
			$logger = $this->get("logger");
			$logger->error("Exception:  Code: " . $e->getCode() . "; Message: " . $e->getMessage(), $e->getTrace());
			if($e->getPrevious() != null && $e->getPrevious() != $e){
				$p = $e->getPrevious();
				$logger->error("Exception:  Code: " . $p->getCode() . "; Message: " . $p->getMessage(), $p->getTrace());
			}
			// replace the callback_url with the referrer.
			if(isset($_SERVER['HTTP_REFERER'])){
				$this->hybridAuth->storage()->set( "hauth_session.$provider_id.hauth_return_to", $_SERVER['HTTP_REFERER'] );
			} else {
				// or go back in the browser-history via js
				return new Response("<html><body onload='window.history.back();'><h1>An Error occured.</h1>Going back one step in the browser history.</body></html>", 500);				
			}
		}
		return $this->returnToCallbackUrl($provider_id);
	}

	/**
	 * define:endpoint step 3.1 and 3.2
	 */
	private function processAuthDone() {

		$this->authInit();

		$provider_id = trim( strip_tags( $this->requestQuery->get("hauth_done") ) );

		$hauth = $this->hybridAuth->setup( $provider_id );

		if( ! $hauth ) {
			\Hybrid_Logger::error( "Endpoint: Invalid parameter on hauth_done!" );

			$hauth->adapter->setUserUnconnected();

			return new Response("Invalid parameter! Please return to the login page and try again.", 404, array(header( "HTTP/1.0 404 Not Found" )));
		}

		try {
			\Hybrid_Logger::info( "Endpoint: call adapter [{$provider_id}] loginFinish() " );

			$hauth->adapter->loginFinish();

			$this->getAzineHybridAuth()->storeHybridAuthSessionData($this->hybridAuth->getSessionData());
		}
		catch( \Exception $e ){
			\Hybrid_Logger::error( "Exception:" . $e->getMessage()."\n\n".$e->getTraceAsString() );
			\Hybrid_Error::setError( $e->getMessage(), $e->getCode(), $e->getTraceAsString(), $e->getPrevious());

			$hauth->adapter->setUserUnconnected();
			
		}

		\Hybrid_Logger::info( "Endpoint: job done. retrun to callback url." );

		return $this->returnToCallbackUrl($provider_id);
	}

	private function authInit() {

		if ( ! $this->initDone) {
			$this->initDone = TRUE;

			# Init Hybrid_Auth
			try {
				if(!class_exists("Hybrid_Storage")){
					require_once realpath( dirname( __FILE__ ) )  . "/Storage.php";
				}

				$storage = new \Hybrid_Storage();

				// Check if Hybrid_Auth session already exist
				if ( ! $storage->config( "CONFIG" ) ) {
					return new Response("You cannot access this page directly.", 500, array(header( "HTTP/1.0 500 Server Error" )));
				}

				$this->hybridAuth->initialize( $storage->config( "CONFIG" ) );
			}
			catch ( \Exception $e ){
				\Hybrid_Logger::error( "Endpoint: Error while trying to init Hybrid_Auth" );

				return new Response("Oophs. Error!", 500, array(header( "HTTP/1.0 500 Server Error" )));
			}
		}
	}

	private function returnToCallbackUrl($provider_id) {
		// get the stored callback url
		$callback_url = $this->hybridAuth->storage()->get( "hauth_session.$provider_id.hauth_return_to" );

		// remove some unneeded stored data
		$this->hybridAuth->storage()->delete( "hauth_session.$provider_id.hauth_return_to"    );
		$this->hybridAuth->storage()->delete( "hauth_session.$provider_id.hauth_endpoint"     );
		$this->hybridAuth->storage()->delete( "hauth_session.$provider_id.id_provider_params" );

		// back to home
		return new RedirectResponse($callback_url);
	}

}
