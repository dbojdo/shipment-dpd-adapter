<?php
/**
 * File SenderMapperTest.php
 * Created at: 2017-09-10 16:54
 *
 * @author Daniel Bojdo <daniel.bojdo@web-it.eu>
 */

namespace Webit\Shipment\DpdAdapter\Mapper\OpenUMLF;

use Webit\Addressing\Model\Country;
use Webit\DPDClient\PackagesGeneration\OpenUMLF\Sender;
use Webit\Shipment\Address\SenderAddressInterface;
use Webit\Shipment\DpdAdapter\AbstractTest;

class SenderMapperTest extends AbstractTest
{
    /**
     * @param SenderAddressInterface $sender
     * @param $fid
     * @param Sender $expectedSender
     * @test
     * @dataProvider senders
     */
    public function shouldMapSender(SenderAddressInterface $sender, $fid, Sender $expectedSender)
    {
        $mapper = new SenderMapper($fid);
        $this->assertEquals($expectedSender, $mapper->map($sender));
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
                $fid = $this->randomPositiveInt(9999),
                new Sender(
                    $name,
                    $address,
                    $post,
                    $postCode,
                    $countryCode,
                    $fid
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
    private function sender($name, $address, $post, $postCode, $countryCode)
    {
        $sender = $this->prophesize('Webit\Shipment\Address\SenderAddressInterface');

        $sender->getName()->willReturn($name);
        $sender->getAddress()->willReturn($address);
        $sender->getPost()->willReturn($post);
        $sender->getPostCode()->willReturn($postCode);
        $sender->getCountry()->willReturn(new Country('whatever', $countryCode));

        return $sender->reveal();
    }
}
