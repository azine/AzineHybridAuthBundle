<?php
namespace Azine\HybridAuthBundle\Services;

use Azine\HybridAuthBundle\Entity\HybridAuthSessionData;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

class AzineHybridAuthTest extends TestCase
{

    private $router;
    private $entityManager;
    private $user;
    private $azineHybridAuth;


    protected function setUp()
    {
        $this->user = $this->getMockBuilder('FOS\UserBundle\Model\User')->getMock();

        $this->router = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Routing\Router')
            ->disableOriginalConstructor()
            ->getMock();

        $this->entityManager = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $config = array('endpoint_route' => 'route', 'providers' => 'providers', 'debug_mode' => 'debug_mode', 'debug_file' => 'debug_file');
        $this->azineHybridAuth = new AzineHybridAuth($this->router, $this->user, $this->entityManager,
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

        $this->assertEquals(true, $isExpired);
    }

    public function testIsNotExpiredSession()
    {

        $sessionData = new HybridAuthSessionData();
        $sessionData->setUsername('dominik');

        $expirationDate = new \DateTime();
        $expirationDate->modify('+ 1 day');

        $sessionData->setExpiresAt($expirationDate);
        $isExpired = $this->azineHybridAuth->isExpiredSession($sessionData);

        $this->assertEquals(false, $isExpired);
    }

}