<?php

namespace Azine\HybridAuthBundle\Services;

use Azine\HybridAuthBundle\Entity\UserContact;

interface ContactFilter
{
    /**
     * @param array of UserContacts
     * @param array of FilterOptions
     *
     * @return array of UserContact
     */
    public function filter(array $contacts, array $filterParams);
}
