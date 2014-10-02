<?php
namespace Azine\MailgunWebhooksBundle\Tests\Controller;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AzineHybridAuthJsonControllerTest extends WebTestCase {

    public function testJsonResponse() {
        $this->checkApplication();

    }

    /**
     * Load the url and login if required.
     * @param  string  $url
     * @param  string  $username
     * @param  Client  $client
     * @return Crawler $crawler of the page of the url or the page after the login
     */
    private function loginUserIfRequired(Client $client, $url, $username = "dominik", $password = "lkjlkjlkjlkj")
    {
        // try to get the url
        $client->followRedirects();
        $crawler = $client->request("GET", $url);

        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Status-Code 200 expected.");

        // if redirected to a login-page, login as admin-user
        if ($crawler->filter("input")->count() == 5 && $crawler->filter("#username")->count() == 1 && $crawler->filter("#password")->count() == 1 ) {

            // set the password of the admin
            $userProvider = $this->getContainer()->get('fos_user.user_provider.username_email');
            $user = $userProvider->loadUserByUsername($username);
            $user->setPlainPassword($password);
            $user->addRole("ROLE_ADMIN");

            $userManager = $this->getContainer()->get('fos_user.user_manager');
            $userManager->updateUser($user);

            $crawler = $crawler->selectButton("Login");
            $form = $crawler->form();
            $form->get('_username')->setValue($username);
            $form->get('_password')->setValue($password);
            $crawler = $client->submit($form);
        }

        $this->assertEquals(200, $client->getResponse()->getStatusCode(),"Login failed.");
        $client->followRedirects(false);

        $this->assertStringEndsWith($url, $client->getRequest()->getUri(), "Login failed or not redirected to requested url: $url vs. ".$client->getRequest()->getUri());

        return $crawler;
    }

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Get the current container
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private function getContainer()
    {
        if ($this->container == null) {
            $this->container = static::$kernel->getContainer();
        }

        return $this->container;
    }

    /**
     * @return UrlGeneratorInterface
     */
    private function getRouter()
    {
        return $this->getContainer()->get('router');
    }

    /**
     * @return EntityManager
     */
    private function getEntityManager()
    {
        return $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * @return EventDispatcher
     */
    private function getEventDispatcher()
    {
        return $this->getContainer()->get("event_dispatcher");
    }

    /**
     * Check if the current setup is a full application.
     * If not, mark the test as skipped else continue.
     */
    private function checkApplication()
    {
        try {
            static::$kernel = static::createKernel(array());
        } catch (\RuntimeException $ex) {
            $this->markTestSkipped("There does not seem to be a full application available (e.g. running tests on travis.org). So this test is skipped.");

            return;
        }
    }
}
