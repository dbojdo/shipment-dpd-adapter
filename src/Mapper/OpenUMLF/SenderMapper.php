<?php

namespace Webit\Shipment\DpdAdapter\Mapper\OpenUMLF;

use Webit\DPDClient\PackagesGeneration\OpenUMLF\Sender;
use Webit\Shipment\Address\SenderAddressInterface;
use Webit\Shipment\DpdAdapter\Mapper\PostCodeSanitiser;

class SenderMapper
{
    /** @var PostCodeSanitiser */
    private $postCodeSanitiser;

    /** @var int */
    private $fid;

    /**
     * SenderMapper constructor.
     * @param PostCodeSanitiser $postCodeSanitiser
     * @param int $fid
     */
    public function __construct(PostCodeSanitiser $postCodeSanitiser, $fid)
    {
        $this->postCodeSanitiser = $postCodeSanitiser;
        $this->fid = (int)$fid;
    }

    /**
     * @param SenderAddressInterface $address
     * @return Sender
     */
    public function map(SenderAddressInterface $address = null)
    {
        if (! $address) {
            return Sender::fromFid($this->fid);
        }

        return new Sender(
            $address->getName(),
            $address->getAddress(),
            $address->getPost(),
            $this->postCodeSanitiser->sanitise($address->getPostCode()),
            $address->getCountry()->getIsoCode(),
            $this->fid
        );
    }
}