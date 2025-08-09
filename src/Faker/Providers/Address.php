<?php

namespace TddWizard\Fixtures\Faker\Providers;

use Faker\Provider\Address as FakerAddress;

class Address extends FakerAddress
{
    /**
     * @example 'Sashabury'
     *
     * @return string
     */
    public function city()
    {
        $result = parent::city();

        // use A-Z, a-z, 0-9, -, ', spaces, if other characters are used, they will be removed
        $result = preg_replace('/[^A-Za-z0-9\-\' ]/', '', $result);

        return $result;
    }
}
