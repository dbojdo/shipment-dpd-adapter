<?php
/**
 * File ReceiverMapperTest.php
 * Created at: 2017-09-10 17:05
 *
 * @author Daniel Bojdo <daniel.bojdo@web-it.eu>
 */

namespace Webit\Shipment\DpdAdapter\Mapper\OpenUMLF;

use Webit\Addressing\Model\Country;
use Webit\DPDClient\PackagesGeneration\OpenUMLF\Receiver;
use Webit\Shipment\Address\DeliveryAddressInterface;
use Webit\Shipment\DpdAdapter\AbstractTest;

class ReceiverMapperTest extends AbstractTest
{
    /**
     * @param DeliveryAddressInterface $deliveryAddress
     * @param Receiver $expectedReceiver
     * @test
     * @dataProvider senders
     */
    public function shouldMapReceiver(DeliveryAddressInterface $deliveryAddress, Receiver $expectedReceiver)
    {
        $mapper = new ReceiverMapper();
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
