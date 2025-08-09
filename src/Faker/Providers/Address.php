<?php

namespace TddWizard\Fixtures\Faker\Providers;

use Faker\Provider\Address as FakerAddress;

class Address extends FakerAddress
{
    public function city()
    {
        // find the provider that would be used if our provider wasn't present
        $providers = $this->generator->getProviders();

        // iterate in reverse (mimic Generator::format lookup order),
        // skip $this to avoid calling ourselves
        foreach (array_reverse($providers) as $provider) {
            if ($provider === $this) {
                continue;
            }

            if (is_callable([$provider, 'city'])) {
                $result = $provider->city();
                // sanitize: keep A-Z a-z 0-9 - ' and space
                return preg_replace('/[^\p{L}\p{N}\-\' ]/u', '', $result);
            }
        }

        // fallback: use parent (should be rare)
        $result = parent::city();
        return preg_replace('/[^\p{L}\p{N}\-\' ]/u', '', $result);
    }
}
