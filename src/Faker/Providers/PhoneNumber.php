<?php

namespace TddWizard\Fixtures\Faker\Providers;

class PhoneNumber extends \Faker\Provider\PhoneNumber
{
    public function phoneNumber(): string
    {
        $phoneNumber = parent::phoneNumber();

        // use 0-9, +, -, (, ) and space, if other characters are used, they will be removed
        $phoneNumber = preg_replace('/[^0-9+\-\(\) ]/', '', $phoneNumber);

        return $phoneNumber;
    }
}
