<?php

namespace Webit\Shipment\DpdAdapter;

use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Webit\DPDClient\DPDInfoServices\Common\Exception\DPDInfoServicesException;
use Webit\DPDClient\DPDInfoServices\CustomerEvents\EventsSelectTypeEnum;
use Webit\DPDClient\DPDServices\Client as ServicesClient;
use Webit\DPDClient\DPDInfoServices\Client as InfoServicesClient;
use Webit\DPDClient\DPDInfoServices\CustomerEvents\CustomerEventsResponseV3;
use Webit\DPDClient\DPDInfoServices\CustomerEvents\CustomerEventV3;
use Webit\Shipment\Consignment\ConsignmentStatusList;
use Webit\Shipment\DpdAdapter\Mapper\OpenUMLF\OpenUMLFMapper;
use Webit\Shipment\DpdAdapter\Mapper\ParcelStatusMapper;
use Webit\Shipment\DpdAdapter\Mapper\PickupSenderProvider;
use Webit\Shipment\DpdAdapter\Tracking\TrackingUrlProvider;
use Webit\Shipment\DpdAdapter\Vendor\VendorFactory;
use Webit\Shipment\Parcel\Parcel;

class ShipmentDpdAdapterTest extends AbstractTest
{
    /** @var VendorFactory|ObjectProphecy */
    private $vendorFactory;

    /** @var TrackingUrlProvider|ObjectProphecy */
    private $trackingUrlProvider;

    /** @var OpenUMLFMapper|ObjectProphecy */
    private $openUmlfMapper;

    /** @var PickupSenderProvider|ObjectProphecy */
    private $pickupSenderProvider;

    /** @var ParcelStatusMapper|ObjectProphecy */
    private $parcelStatusMapper;

    /** @var int */
    private $fid;

    /** @var string */
    private $language;

    /** @var ObjectProphecy|ServicesClient */
    private $servicesClient;

    /** @var ObjectProphecy|InfoServicesClient */
    private $infoServicesClient;

    /** @var ShipmentDpdAdapter */
    private $adapter;

    protected function setUp()
    {
        $this->vendorFactory = $this->prophesize('Webit\Shipment\DpdAdapter\Vendor\VendorFactory');
        $this->trackingUrlProvider = $this->prophesize('Webit\Shipment\DpdAdapter\Tracking\TrackingUrlProvider');
        $this->openUmlfMapper = $this->prophesize('Webit\Shipment\DpdAdapter\Mapper\OpenUMLF\OpenUMLFMapper');
        $this->pickupSenderProvider = $this->prophesize('Webit\Shipment\DpdAdapter\Mapper\PickupSenderProvider');
        $this->parcelStatusMapper = $this->prophesize('Webit\Shipment\DpdAdapter\Mapper\ParcelStatusMapper');
        $this->servicesClient = $this->prophesize('Webit\DPDClient\DPDServices\Client');
        $this->infoServicesClient = $this->prophesize('Webit\DPDClient\DPDInfoServices\Client');
        $this->fid = $this->randomPositiveInt();
        $this->language = 'PL';

        $this->adapter = new ShipmentDpdAdapter(
            $this->vendorFactory->reveal(),
            $this->trackingUrlProvider->reveal(),
            $this->openUmlfMapper->reveal(),
            $this->pickupSenderProvider->reveal(),
            $this->parcelStatusMapper->reveal(),
            $this->servicesClient->reveal(),
            $this->infoServicesClient->reveal(),
            $this->fid,
            $this->language
        );
    }

    /**
     * @test
     * @dataProvider parcelStatues
     * @param CustomerEventV3|null $customerEventV3
     * @param null $exceptedParcelStatus
     */
    public function shouldSynchroniseParcelStatus(CustomerEventV3 $customerEventV3 = null, $exceptedParcelStatus = null)
    {
        $apiResponse = $this->eventsForWaybillResponse($customerEventV3);

        $parcel = new Parcel();
        $parcel->setStatus($initStatus = ConsignmentStatusList::STATUS_DISPATCHED);
        $parcel->setNumber($waybill = $this->randomString());

        $this->infoServicesClient->getEventsForWaybillV1(
            $waybill,
            EventsSelectTypeEnum::all(),
            $this->language
        )->willReturn($apiResponse);

        $exceptedParcelStatus = $exceptedParcelStatus ?: $initStatus;
        if ($customerEventV3) {
            $this->parcelStatusMapper->map($customerEventV3->businessCode())->willReturn($exceptedParcelStatus);
        }

        $this->adapter->synchronizeParcelStatus($parcel);

        $this->assertEquals($exceptedParcelStatus, $parcel->getStatus());
    }

    public function parcelStatues()
    {
        return array(
            'parcel-not-found' => array(),
            'parcel-found' => array(
                $this->customerEventV3($this->randomPositiveInt()),
                ConsignmentStatusList::STATUS_CONCERNED
            )
        );
    }

    /**
     * @test
     */
    public function shouldNotChangeParcelStatusOnApiException()
    {
        $parcel = new Parcel();
        $parcel->setStatus($initStatus = ConsignmentStatusList::STATUS_DISPATCHED);
        $parcel->setNumber($waybill = $this->randomString());

        $exception = new DPDInfoServicesException();
        $this->infoServicesClient->getEventsForWaybillV1(
            $parcel->getNumber(),
            EventsSelectTypeEnum::all(),
            $this->language
        )->willThrow($exception);

        $this->assertEquals($initStatus, $parcel->getStatus());
    }

    /**
     * @param CustomerEventV3|null $customerEventV3
     * @return CustomerEventsResponseV3
     */
    private function eventsForWaybillResponse(CustomerEventV3 $customerEventV3 = null)
    {
        $events = $customerEventV3 ? array($customerEventV3, $this->customerEventV3(), $this->customerEventV3()) : array();

        return new CustomerEventsResponseV3($this->randomString(), $events);
    }

    private function customerEventV3($businessCode = null, $waybill = null)
    {
        return new CustomerEventV3(
            $this->randomPositiveInt(),
            $businessCode ?: (string)$this->randomPositiveInt(),
            $waybill ?: $this->randomString(),
            $this->randomString(),
            date('Y-m-d H:i:s'),
            $this->randomString(3),
            'PL',
            $this->randomString(),
            $this->randomString(),
            $this->randomPositiveInt(),
            array()
        );
    }
}
