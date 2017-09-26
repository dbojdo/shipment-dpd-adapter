<?php

namespace Webit\Shipment\DpdAdapter\Mapper;

use Webit\DPDClient\DPDInfoServices\CustomerEvents\BusinessCodes;
use Webit\Shipment\Consignment\ConsignmentStatusList;

class ParcelStatusMapper
{
    /**
     * @param string $businessCode
     * @return string
     */
    public function map($businessCode)
    {
        if (in_array($businessCode, BusinessCodes::delivered())) {
            return ConsignmentStatusList::STATUS_DELIVERED;
        }

        if ($businessCode == BusinessCodes::PARCEL_REGISTERED_NOT_DISPATCHED_030103) {
            return ConsignmentStatusList::STATUS_DISPATCHED;
        }

        if ((int)$businessCode < (int)BusinessCodes::PARCEL_DELIVERED_190101) {
            return ConsignmentStatusList::STATUS_COLLECTED;
        }

        return ConsignmentStatusList::STATUS_CONCERNED;
    }
}