<?php

declare(strict_types=1);

namespace TddWizard\Fixtures\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\Dir as Directory;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Builder to be used by fixtures
 */
class CustomerBuilder
{
    /**
     * @var CustomerInterface
     */
    private $customer;

    /**
     * @var string
     */
    private $password;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var AddressBuilder[]
     */
    private $addressBuilders;

    /**
     * @var Encryptor
     */
    private $encryptor;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        CustomerInterface $customer,
        Encryptor $encryptor,
        string $password,
        AddressBuilder ...$addressBuilders
    ) {
        $this->customerRepository = $customerRepository;
        $this->customer = $customer;
        $this->encryptor = $encryptor;
        $this->password = $password;
        $this->addressBuilders = $addressBuilders;
    }

    public function __clone()
    {
        $this->customer = clone $this->customer;
    }

    public static function aCustomer(): CustomerBuilder
    {
        $objectManager = Bootstrap::getObjectManager();
        /** @var CustomerInterface $customer */
        $customer = $objectManager->create(CustomerInterface::class);
        $customer->setWebsiteId(1)
            ->setGroupId(1)
            ->setStoreId(1)
            ->setPrefix('Mr.')
            ->setFirstname('John')
            ->setMiddlename('A')
            ->setLastname('Smith')
            ->setSuffix('Esq.')
            ->setTaxvat('12')
            ->setGender(0);
        $password = 'Test#123';
        return new self(
            $objectManager->create(CustomerRepositoryInterface::class),
            $customer,
            $objectManager->create(Encryptor::class),
            $password
        );
    }

    /**
     * Bulk-assign data directly to the customer object.
     *
     * @param array<string, mixed> $data
     * @return CustomerBuilder
     */
    public function withData(array $data): CustomerBuilder
    {
        $builder = clone $this;
        foreach ($data as $key => $value) {
            if (method_exists($builder->customer, 'set' . ucfirst($key))) {
                $builder->customer->{'set' . ucfirst($key)}($value);
            } else {
                $builder->customer->setData($key, $value);
            }
        }
        return $builder;
    }

    public function withPassword(string $password): CustomerBuilder
    {
        $builder = clone $this;
        $builder->password = $password;
        return $builder;
    }

    public function withAddresses(AddressBuilder ...$addressBuilders): CustomerBuilder
    {
        $builder = clone $this;
        $builder->addressBuilders = $addressBuilders;
        return $builder;
    }

    public function withEmail(string $email): CustomerBuilder
    {
        $builder = clone $this;
        $builder->customer->setEmail($email);
        return $builder;
    }

    public function withGroupId(int $groupId): CustomerBuilder
    {
        $builder = clone $this;
        $builder->customer->setGroupId($groupId);
        return $builder;
    }

    public function withStoreId(int $storeId): CustomerBuilder
    {
        $builder = clone $this;
        $builder->customer->setStoreId($storeId);
        return $builder;
    }

    public function withWebsiteId(int $websiteId): CustomerBuilder
    {
        $builder = clone $this;
        $builder->customer->setWebsiteId($websiteId);
        return $builder;
    }

    public function withPrefix(string $prefix): CustomerBuilder
    {
        $builder = clone $this;
        $builder->customer->setPrefix($prefix);
        return $builder;
    }

    public function withFirstname(string $firstname): CustomerBuilder
    {
        $builder = clone $this;
        $builder->customer->setFirstname($firstname);
        return $builder;
    }

    public function withMiddlename(string $middlename): CustomerBuilder
    {
        $builder = clone $this;
        $builder->customer->setMiddlename($middlename);
        return $builder;
    }

    public function withLastname(string $lastname): CustomerBuilder
    {
        $builder = clone $this;
        $builder->customer->setLastname($lastname);
        return $builder;
    }

    public function withSuffix(string $suffix): CustomerBuilder
    {
        $builder = clone $this;
        $builder->customer->setSuffix($suffix);
        return $builder;
    }

    public function withTaxvat(string $taxvat): CustomerBuilder
    {
        $builder = clone $this;
        $builder->customer->setTaxvat($taxvat);
        return $builder;
    }

    public function withDob(string $dob): CustomerBuilder
    {
        $builder = clone $this;
        $builder->customer->setDob($dob);
        return $builder;
    }

    /**
     * @param mixed[] $values
     * @return CustomerBuilder
     */
    public function withCustomAttributes(array $values): CustomerBuilder
    {
        $builder = clone $this;
        foreach ($values as $code => $value) {
            $builder->customer->setCustomAttribute($code, $value);
        }
        return $builder;
    }

    /**
     * Adds an image to the customer by copying it from the module's _files/images directory
     *
     * @param string $fileName
     * @param string $attributeCode
     * @return $this
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function withImage(string $fileName, string $attributeCode): CustomerBuilder
    {
        $builder = clone $this;

        $objectManager = Bootstrap::getObjectManager();
        $filesystem = $objectManager->get(\Magento\Framework\Filesystem::class);
        $directoryList = $objectManager->get(\Magento\Framework\App\Filesystem\DirectoryList::class);
        $directory = $objectManager->get(Directory::class);

        // Path to fixture image file in your module's _files/images directory
        $fixtureImagePath = $directory->getDir(moduleName: 'TddWizard_Fixtures')
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '_files'
            . DIRECTORY_SEPARATOR . 'images'
            . DIRECTORY_SEPARATOR . $fileName;

        // Destination path in pub/media/customer
        $mediaDir = $filesystem->getDirectoryWrite($directoryList::MEDIA);
        $destinationPath = 'customer/' . $fileName;
        $absoluteDestination = $mediaDir->getAbsolutePath($destinationPath);

        // Copy image to media folder
        if (!is_dir(dirname($absoluteDestination))) {
            mkdir(dirname($absoluteDestination), 0775, true);
        }
        copy($fixtureImagePath, $absoluteDestination);

        // Set the image path in a custom customer attribute
        $builder->customer->setCustomAttribute($attributeCode, '/' . $fileName);

        return $builder;
    }

    public function withConfirmation(string $confirmation): CustomerBuilder
    {
        $builder = clone $this;
        $builder->customer->setConfirmation($confirmation);
        return $builder;
    }

    /**
     * @return CustomerInterface
     * @throws LocalizedException
     */
    public function build(): CustomerInterface
    {
        $builder = clone $this;
        if (!$builder->customer->getEmail()) {
            $builder->customer->setEmail(sha1(uniqid('', true)) . '@example.com');
        }
        $addresses = array_map(
            function (AddressBuilder $addressBuilder) {
                return $addressBuilder->buildWithoutSave();
            },
            $builder->addressBuilders
        );
        $builder->customer->setAddresses($addresses);
        $customer = $builder->saveNewCustomer();
        /*
         * Magento automatically sets random confirmation key for new account with password.
         * We need to save again with our own confirmation (null for confirmed customer)
         */
        $customer->setConfirmation((string)$builder->customer->getConfirmation());
        return $builder->customerRepository->save($customer);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod) False positive: the method is used in build() on the cloned builder
     *
     * @return CustomerInterface
     * @throws LocalizedException
     */
    private function saveNewCustomer(): CustomerInterface
    {
        return $this->customerRepository->save($this->customer, $this->encryptor->getHash($this->password, true));
    }
}
