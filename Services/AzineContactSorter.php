<?php

namespace Azine\HybridAuthBundle\Services;

use Azine\HybridAuthBundle\Entity\UserContact;

class AzineContactSorter implements ContactSorter
{
    /**
     * In this default implementation, all the contacts are sorted in
     * alphabetic order by their lastName.
     *
     * @param UserContact $a
     *
     * @return UserContact $b
     */
    public function compare(UserContact $a, UserContact $b)
    {
        return strnatcasecmp($a->lastName, $b->lastName);
    }
}
