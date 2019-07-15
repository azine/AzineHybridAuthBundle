<?php

namespace Azine\HybridAuthBundle\Controller;

use Hybridauth\Adapter\AbstractAdapter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HybridEndPointController extends Controller
{
    /**
     * @var ParameterBag
     */
    private $requestQuery;

    /**
     * Process the current request.
     *
     * $request - The current request parameters. Leave as NULL to default to use $_REQUEST.
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     */
    public function processAction(Request $request)
    {
        $provider = 'linkedin';
        $cookieName = $this->getAzineHybridAuthService()->getCookieName($provider);

        $adapter = $this->getAzineHybridAuthService()->getInstance($request->cookies->get($cookieName), $provider);

        try {
            $adapter->authenticate();
            $result = $this->getAzineHybridAuthService()->storeHybridAuthSessionData($request, $provider, json_encode($adapter->getAccessToken()));
        } catch (\Exception $e) {
            throw new \Exception("Unable to create adapter for provider '$provider'. Is it configured properly?", $e->getCode(), $e);
        }

        $response = $this->returnToCallbackUrl($adapter, $provider);

        if ($result instanceof Cookie) {
            $response->headers->setCookie($result);
        }

        return $response;
    }

    private function returnToCallbackUrl(AbstractAdapter $adapter, $provider)
    {
        // get the stored callback url
        $callback_url = $adapter->getStorage()->get("hauth_session.$provider.hauth_return_to");

        // remove some unneeded stored data
        $adapter->getStorage()->delete("hauth_session.$provider.hauth_return_to");
        $adapter->getStorage()->delete("hauth_session.$provider.hauth_endpoint");
        $adapter->getStorage()->delete("hauth_session.$provider.id_provider_params");

        // back to home
        return new RedirectResponse($callback_url);
    }

    /**
     * @return AzineHybridAuth
     */
    private function getAzineHybridAuthService()
    {
        return $this->get('azine_hybrid_auth_service');
    }
}
