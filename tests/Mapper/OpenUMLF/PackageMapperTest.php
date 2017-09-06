<?php
/**
 * File PackageMapperTest.php
 * Created at: 2017-09-10 17:11
 *
 * @author Daniel Bojdo <daniel.bojdo@web-it.eu>
 */

namespace Webit\Shipment\DpdAdapter\Mapper\OpenUMLF;

use Doctrine\Common\Collections\ArrayCollection;
use Prophecy\Prophecy\ObjectProphecy;
use Webit\DPDClient\PackagesGeneration\OpenUMLF\Package;
use Webit\DPDClient\PackagesGeneration\OpenUMLF\PayerType;
use Webit\DPDClient\PackagesGeneration\OpenUMLF\Receiver;
use Webit\DPDClient\PackagesGeneration\OpenUMLF\Sender;
use Webit\Shipment\DpdAdapter\AbstractTest;

class PackageMapperTest extends AbstractTest
{
    /**
     * @var PackageMapper
     */
    private $packageMapper;

    /**
     * @var ObjectProphecy
     */
    private $receiverMapper;

    /**
     * @var ObjectProphecy
     */
    private $senderMapper;

    /**
     * @var ObjectProphecy
     */
    private $parcelMapper;

    /**
     * @var ObjectProphecy
     */
    private $servicesMapper;

    protected function setUp()
    {
        $this->receiverMapper = $this->prophesize('Webit\Shipment\DpdAdapter\Mapper\OpenUMLF\ReceiverMapper');
        $this->senderMapper = $this->prophesize('Webit\Shipment\DpdAdapter\Mapper\OpenUMLF\SenderMapper');
        $this->parcelMapper = $this->prophesize('Webit\Shipment\DpdAdapter\Mapper\OpenUMLF\ParcelMapper');
        $this->servicesMapper = $this->prophesize('Webit\Shipment\DpdAdapter\Mapper\OpenUMLF\ServicesMapper');

        $this->packageMapper = new PackageMapper(
            $this->receiverMapper->reveal(),
            $this->senderMapper->reveal(),
            $this->parcelMapper->reveal(),
            $this->servicesMapper->reveal()
        );
    }

    /**
     * @test
     */
    public function shouldMapPackage()
    {
        $consignment = $this->prophesize('Webit\Shipment\Consignment\ConsignmentInterface');
        $consignment->getReference()->willReturn($reference = $this->randomString());
        $consignment->getSenderAddress()->willReturn($senderAddress = $this->prophesize('Webit\Shipment\Address\SenderAddressInterface')->reveal());
        $consignment->getDeliveryAddress()->willReturn($deliveryAddress = $this->prophesize('Webit\Shipment\Address\DeliveryAddressInterface')->reveal());

        $parcels = new ArrayCollection(array(
            $this->prophesize('Webit\Shipment\Parcel\ParcelInterface')->reveal(),
            $this->prophesize('Webit\Shipment\Parcel\ParcelInterface')->reveal()
        ));

        $mappedParcels = array(
            $this->prophesize('Webit\Shipment\Parcel\Parcel')->reveal(),
            $this->prophesize('Webit\Shipment\Parcel\Parcel')->reveal()
        );

        $consignment->getParcels()->willReturn($parcels);

        $this->parcelMapper->map($parcels->get(0))->willReturn($mappedParcels[0]);
        $this->parcelMapper->map($parcels->get(1))->willReturn($mappedParcels[1]);

        $this->receiverMapper->map($deliveryAddress)
        ->willReturn($mappedReceiver = Receiver::fromFid($this->randomPositiveInt(9999)));

        $this->senderMapper->map($senderAddress)
        ->willReturn($mappedSender = Sender::fromFid($this->randomPositiveInt(9999)));
        $this->servicesMapper->map($consignment)->willReturn($mappedServices = $this->prophesize('Webit\DPDClient\PackagesGeneration\OpenUMLF\Services')->reveal());

        $this->assertEquals(
            new Package(
                $mappedReceiver,
                $mappedSender,
                PayerType::sender(),
                $mappedParcels,
                $mappedServices,
                $reference
            ),
            $this->packageMapper->map($consignment->reveal())
        );
    }
}
