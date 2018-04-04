<?php

namespace Azine\HybridAuthBundle\Services;

use Azine\HybridAuthBundle\Entity\UserContact;

class AzineContactFilter implements ContactFilter
{
    /**
     * Dummy implementation of the ContactFilter: doesn't do anything.
     *
     * @param $contacts array of UserContacts
     * @param $filterParams array of FilterOptions
     *
     * @return array of UserContact (same as $contacts
     */
    public function filter(array $contacts, array $filterParams)
    {
        return $contacts;
    }
}
