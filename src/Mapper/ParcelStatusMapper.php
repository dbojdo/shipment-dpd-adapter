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

        if (preg_match('/^03/', $businessCode)) {
            return ConsignmentStatusList::STATUS_DISPATCHED;
        }

        if ($businessCode == BusinessCodes::PARCEL_EMAIL_NOTIFICATION_SENT) {
            throw AmbiguousParcelStatusException::create($businessCode);
        }

        if (preg_match('/^[013]/', $businessCode)) {
            return ConsignmentStatusList::STATUS_COLLECTED;
        }

        return ConsignmentStatusList::STATUS_CONCERNED;
    }
}