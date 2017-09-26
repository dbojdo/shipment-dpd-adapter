<?php

namespace Webit\Shipment\DpdAdapter\Mapper\OpenUMLF;

use Webit\DPDClient\DPDServices\PackagesGeneration\OpenUMLF\Receiver;
use Webit\Shipment\Address\DeliveryAddressInterface;
use Webit\Shipment\DpdAdapter\Mapper\PostCodeSanitiser;

class ReceiverMapper
{
    /**
     * @var PostCodeSanitiser
     */
    private $postCodeSanitiser;

    /**
     * ReceiverMapper constructor.
     * @param PostCodeSanitiser $postCodeSanitiser
     */
    public function __construct(PostCodeSanitiser $postCodeSanitiser)
    {
        $this->postCodeSanitiser = $postCodeSanitiser;
    }

    /**
     * @param DeliveryAddressInterface $address
     * @return Receiver
     */
    public function map(DeliveryAddressInterface $address)
    {
        $receiver = new Receiver(
            $address->getName(),
            $address->getAddress(),
            $address->getPost(),
            $this->postCodeSanitiser->sanitise($address->getPostCode()),
            $address->getCountry()->getIsoCode(),
            null,
            $address->getContactPerson(),
            $address->getContactPhoneNo(),
            $address->getContactEmail()
        );

        return $receiver;
    }
}
