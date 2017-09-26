<?php

namespace Webit\Shipment\DpdAdapter\Mapper;

use Webit\DPDClient\DPDServices\DPDPickupCallParams\PickupSenderDPPV1;
use Webit\Shipment\Address\DefaultSenderAddressProviderInterface;

class PickupSenderProvider
{
    /** @var DefaultSenderAddressProviderInterface */
    private $defaultSenderProvider;

    /** @var PickupSenderMapper */
    private $pickupSenderMapper;

    /**
     * PickupSenderProvider constructor.
     * @param DefaultSenderAddressProviderInterface $defaultSenderProvider
     * @param PickupSenderMapper $pickupSenderMapper
     */
    public function __construct(
        DefaultSenderAddressProviderInterface $defaultSenderProvider,
        PickupSenderMapper $pickupSenderMapper
    ) {
        $this->defaultSenderProvider = $defaultSenderProvider;
        $this->pickupSenderMapper = $pickupSenderMapper;
    }

    /**
     * @return PickupSenderDPPV1
     */
    public function getPickupSender()
    {
        $sender = $this->defaultSenderProvider->getSender();

        return $this->pickupSenderMapper->map($sender);
    }
}