<?php

namespace Webit\Shipment\DpdAdapter\Mapper;

use Prophecy\Prophecy\ObjectProphecy;
use Webit\Shipment\Address\DefaultSenderAddressProviderInterface;
use Webit\Shipment\DpdAdapter\AbstractTest;

class PickupSenderProviderTest extends AbstractTest
{
    /**
     * @var DefaultSenderAddressProviderInterface|ObjectProphecy
     */
    private $defaultSenderAddressProvider;

    /** @var PickupSenderMapper|ObjectProphecy */
    private $pickupSenderMapper;

    /** @var PickupSenderProvider */
    private $pickupSenderProvider;

    protected function setUp()
    {
        $this->defaultSenderAddressProvider = $this->prophesize('Webit\Shipment\Address\DefaultSenderAddressProviderInterface');
        $this->pickupSenderMapper = $this->prophesize('Webit\Shipment\DpdAdapter\Mapper\PickupSenderMapper');

        $this->pickupSenderProvider = new PickupSenderProvider(
            $this->defaultSenderAddressProvider->reveal(),
            $this->pickupSenderMapper->reveal()
        );
    }

    /**
     * @test
     */
    public function shouldProviderPickupSender()
    {
        $senderAddress = $this->prophesize('Webit\Shipment\Address\SenderAddressInterface')->reveal();
        $pickupSender = $this->prophesize('Webit\DPDClient\DPDServices\DPDPickupCallParams\PickupSenderDPPV1')->reveal();

        $this->defaultSenderAddressProvider->getSender()->willReturn($senderAddress);
        $this->pickupSenderMapper->map($senderAddress)->willReturn($pickupSender);


        $this->assertSame($pickupSender, $this->pickupSenderProvider->getPickupSender());
    }
}
