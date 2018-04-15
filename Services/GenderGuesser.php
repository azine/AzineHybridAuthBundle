<?php

namespace Azine\HybridAuthBundle\Services;

interface GenderGuesser
{
    /**
     * Guess the gender for a given firstname. The guesser will return an array with
     * the sex (m or f) and a confidence indicator (float 0.0 - 1.0, 0 = no confidence, 1 = sure).
     *
     * @param \string $firstName
     * @param int     $looseness
     *
     * @return array(sex => confidence);
     */
    public function guess($firstName, $looseness = 1);

    /**
     * Guess the gender for a given firstname. The guesser will return an array with.
     *
     * @param \string $firstName
     * @param int     $looseness
     *
     * @return \string sex => m | f
     */
    public function gender($firstName, $looseness = 1);
}
