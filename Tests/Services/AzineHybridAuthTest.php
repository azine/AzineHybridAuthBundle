<?php

namespace Azine\HybridAuthBundle\Services;

use Azine\HybridAuthBundle\Entity\HybridAuthSessionData;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class AzineHybridAuthTest extends TestCase
{
    private $azineHybridAuth;

    protected function setUp()
    {
        $user = $this->getMockBuilder('FOS\UserBundle\Model\User')->getMock();

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
            ->setConstructorArgs(array('key', $user))
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
