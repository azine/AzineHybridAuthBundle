<?php

namespace Azine\HybridAuthBundle\Entity;

/**
 * HybridAuthSessionData.
 */
class HybridAuthSessionData
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $sessionData;

    /**
     * @var \DateTime
     */
    private $expiresAt;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username.
     *
     * @param string $username
     *
     * @return HybridAuthSessionData
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set sessionData.
     *
     * @param string $sessionData
     *
     * @return HybridAuthSessionData
     */
    public function setSessionData($sessionData)
    {
        $this->sessionData = $sessionData;

        return $this;
    }

    /**
     * Get sessionData.
     *
     * @return string
     */
    public function getSessionData()
    {
        return $this->sessionData;
    }

    /**
     * @var string
     */
    private $provider;

    /**
     * Set provider.
     *
     * @param string $provider
     *
     * @return HybridAuthSessionData
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Get provider.
     *
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param \DateTime $date
     *
     * @return HybridAuthSessionData
     */
    public function setExpiresAt(\DateTime $date = null)
    {
        $this->expiresAt = $date;

        return $this;
    }

    /**
     * Get expiresAt.
     *
     * @return \DateTime
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }
}
