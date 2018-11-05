<?php

namespace Azine\HybridAuthBundle\Tests\Services;

use Azine\HybridAuthBundle\Entity\HybridAuthSessionData;
use Azine\HybridAuthBundle\Services\AzineHybridAuth;
use Azine\HybridAuthBundle\Tests\AzineTestCase;

class AzineHybridAuthTest extends AzineTestCase
{
    private $azineHybridAuth;

    protected function setUp()
    {
        $router = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Routing\Router')
            ->disableOriginalConstructor()
            ->getMock();

        $entityManager = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $tokenStorage = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $token = $this->getMockBuilder('Symfony\Component\Security\Core\Authentication\Token\AnonymousToken')
            ->disableOriginalConstructor()
            ->getMock();

        $tokenStorage->expects($this->any())
            ->method('getToken')
            ->will($this->returnValue($token));

        $config = array('endpoint_route' => 'route', 'providers' => 'providers', 'debug_mode' => 'debug_mode', 'debug_file' => 'debug_file');
        $this->azineHybridAuth = new AzineHybridAuth($router, $tokenStorage, $entityManager,
            $config, false, true, 55);
    }

    public function testIsExpiredSession()
    {
        $sessionData = new HybridAuthSessionData();
        $sessionData->setUsername('dominik');

        $expirationDate = new \DateTime();
        $expirationDate->modify('- 1 day');

        $sessionData->setExpiresAt($expirationDate);
        $isExpired = $this->azineHybridAuth->isExpiredSession($sessionData);

        $this->assertTrue($isExpired);
    }

    public function testIsNotExpiredSession()
    {
        $sessionData = new HybridAuthSessionData();
        $sessionData->setUsername('dominik');

        $expirationDate = new \DateTime();
        $expirationDate->modify('+ 1 day');

        $sessionData->setExpiresAt($expirationDate);
        $isExpired = $this->azineHybridAuth->isExpiredSession($sessionData);

        $this->assertFalse($isExpired);
    }
}
