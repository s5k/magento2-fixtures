<?php

namespace TddWizard\Fixtures\Faker\Providers;

use Faker\Generator;

class PhoneNumber extends \Faker\Provider\PhoneNumber
{
    /**
     * @var Generator
     */
    protected $generator;

    public function __construct(Generator $generator)
    {
        parent::__construct($generator);
        $this->generator = $generator;
    }

    public function phoneNumber(): string
    {
        $localeProvider = $this->findLocalePhoneNumberProvider();

        if ($localeProvider) {
            $phoneNumber = $localeProvider->phoneNumber();
        } else {
            // fallback to default
            $phoneNumber = parent::phoneNumber();
        }

        // 0-9, +, -, (, ) and space only
        $phoneNumber = preg_replace('/[^0-9+\-\(\)\s]/u', '', $phoneNumber);

        return $phoneNumber;
    }

    /**
     * Finds the locale-specific PhoneNumber provider if available
     */
    private function findLocalePhoneNumberProvider(): ?object
    {
        foreach ($this->generator->getProviders() as $provider) {
            if (
                $provider instanceof \Faker\Provider\PhoneNumber &&
                get_class($provider) !== self::class // avoid recursion
            ) {
                return $provider;
            }
        }
        return null;
    }
}
