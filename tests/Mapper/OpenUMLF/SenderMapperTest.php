<?php

namespace Webit\Shipment\DpdAdapter\Mapper\OpenUMLF;

use Prophecy\Prophecy\ObjectProphecy;
use Webit\Addressing\Model\Country;
use Webit\DPDClient\PackagesGeneration\OpenUMLF\Sender;
use Webit\Shipment\Address\SenderAddressInterface;
use Webit\Shipment\DpdAdapter\AbstractTest;
use Webit\Shipment\DpdAdapter\Mapper\PostCodeSanitiser;

class SenderMapperTest extends AbstractTest
{
    /** @var PostCodeSanitiser|ObjectProphecy */
    private $postCodeSanitiser;

    protected function setUp()
    {
        $this->postCodeSanitiser = $this->prophesize('Webit\Shipment\DpdAdapter\Mapper\PostCodeSanitiser');
    }

    /**
     * @param SenderAddressInterface $sender
     * @param $fid
     * @param Sender $expectedSender
     * @test
     * @dataProvider senders
     */
    public function shouldMapSender(SenderAddressInterface $sender, $fid, Sender $expectedSender)
    {
        $this->postCodeSanitiser->sanitise($sender->getPostCode())->willReturn($expectedSender->postalCode());

        $mapper = new SenderMapper($this->postCodeSanitiser->reveal(), $fid);
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
                    $this->faker()->postcode,
                    $countryCode,
                    $fid
                )
            )
        );
    }
}
