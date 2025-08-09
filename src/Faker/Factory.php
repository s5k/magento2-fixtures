<?php

namespace TddWizard\Fixtures\Faker;

use Faker\Factory as FakerFactory;
use TddWizard\Fixtures\Faker\Providers\Address;
use TddWizard\Fixtures\Faker\Providers\PhoneNumber;

class Factory extends FakerFactory
{
    public static function create($locale = self::DEFAULT_LOCALE)
    {
        $faker = parent::create($locale);
        $faker->addProvider(new PhoneNumber($faker));
        $faker->addProvider(new Address($faker));
        return $faker;
    }
}
