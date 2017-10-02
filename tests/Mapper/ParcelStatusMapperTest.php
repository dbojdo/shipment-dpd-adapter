<?php

namespace Webit\Shipment\DpdAdapter\Mapper;

use Webit\DPDClient\DPDInfoServices\CustomerEvents\BusinessCodes;
use Webit\Shipment\Consignment\ConsignmentStatusList;
use Webit\Shipment\DpdAdapter\AbstractTest;

class ParcelStatusMapperTest extends AbstractTest
{
    /**
     * @var ParcelStatusMapper
     */
    private $mapper;

    protected function setUp()
    {
        $this->mapper = new ParcelStatusMapper();
    }

    /**
     * @param $businessCode
     * @param $expectedStatus
     * @dataProvider statues
     * @test
     */
    public function shouldMapBusinessCodeToStatus($businessCode, $expectedStatus)
    {
        $this->assertEquals($expectedStatus, $this->mapper->map($businessCode));
    }

    public function statues()
    {
        return array(
            'dispatched' => array(
                BusinessCodes::PARCEL_REGISTERED_NOT_DISPATCHED_030103,
                ConsignmentStatusList::STATUS_DISPATCHED
            ),
            'delivered' => array(
                BusinessCodes::PARCEL_DELIVERED_190103,
                ConsignmentStatusList::STATUS_DELIVERED
            ),
            'in transit' => array(
                BusinessCodes::PARCEL_COLLECTED_BY_COURIER_040101,
                ConsignmentStatusList::STATUS_COLLECTED
            ),
            'in transit 1' => array(
                BusinessCodes::PARCEL_DISPATCHED_TO_BE_DELIVERED_170101,
                ConsignmentStatusList::STATUS_COLLECTED
            ),
            'in transit 2' => array(
                BusinessCodes::PARCEL_RECEIVED_BY_DEPOT_330137,
                ConsignmentStatusList::STATUS_COLLECTED
            ),
            'concerned' => array(
                BusinessCodes::PARCEL_RETURN_500611,
                ConsignmentStatusList::STATUS_CONCERNED
            )
        );
    }
}
