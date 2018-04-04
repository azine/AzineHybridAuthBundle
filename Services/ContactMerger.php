<?php

namespace Azine\HybridAuthBundle\Services;

use Azine\HybridAuthBundle\Entity\UserContact;

interface ContactMerger
{
    /**
     * @param array of array( providerId => array of UserContacts)
     *
     * @return array of UserContact
     */
    public function merge(array $newContacts);
}
