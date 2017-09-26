<?php

namespace Webit\Shipment\DpdAdapter\Mapper\OpenUMLF;

use Doctrine\Common\Collections\ArrayCollection;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Webit\DPDClient\DPDServices\PackagesGeneration\OpenUMLF\PackageV2;
use Webit\DPDClient\DPDServices\PackagesGeneration\OpenUMLF\PayerType;
use Webit\DPDClient\DPDServices\PackagesGeneration\OpenUMLF\Receiver;
use Webit\DPDClient\DPDServices\PackagesGeneration\OpenUMLF\Sender;
use Webit\Shipment\Address\SenderAddressInterface;
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

    /**
     * @var \Webit\Shipment\Address\DefaultSenderAddressProviderInterface|ObjectProphecy
     */
    private $defaultSenderAddressProvider;

    protected function setUp()
    {
        $this->receiverMapper = $this->prophesize('Webit\Shipment\DpdAdapter\Mapper\OpenUMLF\ReceiverMapper');
        $this->senderMapper = $this->prophesize('Webit\Shipment\DpdAdapter\Mapper\OpenUMLF\SenderMapper');
        $this->parcelMapper = $this->prophesize('Webit\Shipment\DpdAdapter\Mapper\OpenUMLF\ParcelMapper');
        $this->servicesMapper = $this->prophesize('Webit\Shipment\DpdAdapter\Mapper\OpenUMLF\ServicesMapper');
        $this->defaultSenderAddressProvider = $this->prophesize('Webit\Shipment\Address\DefaultSenderAddressProviderInterface');

        $this->packageMapper = new PackageMapper(
            $this->receiverMapper->reveal(),
            $this->senderMapper->reveal(),
            $this->parcelMapper->reveal(),
            $this->servicesMapper->reveal(),
            $this->defaultSenderAddressProvider->reveal()
        );
    }

    /**
     * @test
     * @senderAddresses
     */
    public function shouldMapPackage(SenderAddressInterface $senderAddress = null)
    {
        $consignment = $this->prophesize('Webit\Shipment\Consignment\ConsignmentInterface');
        $consignment->getReference()->willReturn($reference = $this->randomString());
        $consignment->getSenderAddress()->willReturn($senderAddress);

        if (!$senderAddress) {
            $senderAddress = $this->prophesize('Webit\Shipment\Address\SenderAddressInterface')->reveal();
            $this->defaultSenderAddressProvider->getSender()->willReturn($senderAddress);
        }

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
        $this->servicesMapper->map($consignment)->willReturn($mappedServices = $this->prophesize('Webit\DPDClient\DPDServices\PackagesGeneration\OpenUMLF\Services')->reveal());

        $consignment->addVendorData('reference', Argument::type('string'))->willReturn(null);

        $package = $this->packageMapper->map($consignment->reveal());
        $this->assertEquals(
            new PackageV2(
                $mappedReceiver,
                $mappedSender,
                PayerType::sender(),
                $mappedParcels,
                $mappedServices,
                $package->reference(),
                null,
                null,
                $reference
            ),
            $package
        );
    }


    public function senderAddresses()
    {
        return array(
            array(
                $this->prophesize('Webit\Shipment\Address\SenderAddressInterface')->reveal(),
                null
            )
        );
    }
}
