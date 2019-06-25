<?php

namespace Azine\HybridAuthBundle\Controller;

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

        $response = new RedirectResponse($this->generateUrl('user_edit'));

        if ($result instanceof Cookie) {
            $response->headers->setCookie($result);
        }

        return $response;
    }

    /**
     * @return AzineHybridAuth
     */
    private function getAzineHybridAuthService()
    {
        return $this->get('azine_hybrid_auth_service');
    }
}
