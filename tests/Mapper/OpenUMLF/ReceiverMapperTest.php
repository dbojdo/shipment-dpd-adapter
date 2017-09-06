<?php

namespace Webit\Shipment\DpdAdapter\Mapper\OpenUMLF;

use Prophecy\Prophecy\ObjectProphecy;
use Webit\Addressing\Model\Country;
use Webit\DPDClient\PackagesGeneration\OpenUMLF\Receiver;
use Webit\Shipment\Address\DeliveryAddressInterface;
use Webit\Shipment\DpdAdapter\AbstractTest;
use Webit\Shipment\DpdAdapter\Mapper\PostCodeSanitiser;

class ReceiverMapperTest extends AbstractTest
{
    /** @var PostCodeSanitiser|ObjectProphecy */
    private $postCodeSanitiser;

    protected function setUp()
    {
        $this->postCodeSanitiser = $this->prophesize('Webit\Shipment\DpdAdapter\Mapper\PostCodeSanitiser');
    }

    /**
     * @param DeliveryAddressInterface $deliveryAddress
     * @param Receiver $expectedReceiver
     * @test
     * @dataProvider senders
     */
    public function shouldMapReceiver(DeliveryAddressInterface $deliveryAddress, Receiver $expectedReceiver)
    {
        $this->postCodeSanitiser->sanitise($deliveryAddress->getPostCode())->willReturn($expectedReceiver->postalCode());

        $mapper = new ReceiverMapper($this->postCodeSanitiser->reveal());
        $this->assertEquals($expectedReceiver, $mapper->map($deliveryAddress));
    }

    public function senders()
    {
        return array(
            array(
                $this->deliveryAddress(
                    $name = $this->faker()->name(),
                    $address = $this->faker()->streetAddress,
                    $post = $this->faker()->city,
                    $postCode = $this->faker()->postcode,
                    $countryCode = $this->faker()->countryCode,
                    $contactPerson = $this->faker()->name,
                    $phoneNo = $this->faker()->phoneNumber,
                    $email = $this->faker()->email
                ),
                new Receiver(
                    $name,
                    $address,
                    $post,
                    $postCode,
                    $countryCode,
                    null,
                    $contactPerson,
                    $phoneNo,
                    $email
                )
            )
        );
    }

    /**
     * @param $name
     * @param $address
     * @param $post
     * @param $postCode
     * @param $countryCode
     * @return object
     */
    private function deliveryAddress($name, $address, $post, $postCode, $countryCode, $contactPerson, $phoneNo, $email)
    {
        $deliveryAddress = $this->prophesize('Webit\Shipment\Address\DeliveryAddressInterface');

        $deliveryAddress->getName()->willReturn($name);
        $deliveryAddress->getAddress()->willReturn($address);
        $deliveryAddress->getPost()->willReturn($post);
        $deliveryAddress->getPostCode()->willReturn($postCode);
        $deliveryAddress->getCountry()->willReturn(new Country('whatever', $countryCode));
        $deliveryAddress->getContactPerson()->willReturn($contactPerson);
        $deliveryAddress->getContactPhoneNo()->willReturn($phoneNo);
        $deliveryAddress->getContactEmail()->willReturn($email);

        return $deliveryAddress->reveal();
    }
}
