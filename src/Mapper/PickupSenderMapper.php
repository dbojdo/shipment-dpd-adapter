<?php

namespace Webit\Shipment\DpdAdapter\Mapper;

use Webit\DPDClient\DPDServices\DPDPickupCallParams\PickupSenderDPPV1;
use Webit\Shipment\Address\SenderAddressInterface;

class PickupSenderMapper
{
    /** @var PostCodeSanitiser */
    private $postCodeSanitiser;

    /**
     * PickupSenderMapper constructor.
     * @param PostCodeSanitiser $postCodeSanitiser
     */
    public function __construct(PostCodeSanitiser $postCodeSanitiser)
    {
        $this->postCodeSanitiser = $postCodeSanitiser;
    }

    /**
     * @param SenderAddressInterface $sender
     * @return PickupSenderDPPV1
     */
    public function map(SenderAddressInterface $sender)
    {
        return new PickupSenderDPPV1(
            $sender->getName(),
            '',
            $sender->getAddress(),
            $sender->getPost(),
            $this->postCodeSanitiser->sanitise($sender->getPostCode())
        );
    }
}