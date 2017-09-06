<?php

namespace Webit\Shipment\DpdAdapter\Mapper;

use Webit\DPDClient\DPDPickupCallParams\PickupSenderDPPV1;
use Webit\Shipment\Address\SenderAddressInterface;
use Webit\Shipment\DpdAdapter\AbstractTest;

class PickupSenderMapperTest extends AbstractTest
{
    /** @var PostCodeSanitiser|ObjectProphecy */
    private $postCodeSanitiser;

    /** @var PickupSenderMapper */
    private $mapper;

    protected function setUp()
    {
        $this->postCodeSanitiser = $this->prophesize('Webit\Shipment\DpdAdapter\Mapper\PostCodeSanitiser');
        $this->mapper = new PickupSenderMapper($this->postCodeSanitiser->reveal());
    }

    /**
     * @param SenderAddressInterface $deliveryAddress
     * @param PickupSenderDPPV1 $expectedPickupSender
     * @test
     * @dataProvider senders
     */
    public function shouldMapPickupSender(SenderAddressInterface $deliveryAddress, PickupSenderDPPV1 $expectedPickupSender)
    {
        $this->postCodeSanitiser->sanitise($deliveryAddress->getPostCode())->willReturn($expectedPickupSender->senderPostalCode());

        $this->assertEquals($expectedPickupSender, $this->mapper->map($deliveryAddress));
    }

    public function senders()
    {
        return array(
            array(
                $this->sender(
                    $name = $this->faker()->name(),
                    $address = $this->faker()->streetAddress,
                    $post = $this->faker()->city,
                    $postCode = $this->faker()->postcode,
                    $countryCode = $this->faker()->countryCode
                ),
                new PickupSenderDPPV1(
                    $name,
                    null,
                    $address,
                    $post,
                    $this->faker()->postcode
                )
            )
        );
    }
}
