<?php

namespace Webit\Shipment\DpdAdapter;

use Webit\DPDClient\DPDServices\PackagesPickupCall\PackagesPickupCallResponseV3;

class PickupCallException extends DpdAdapterException
{
    /**
     * @var PackagesPickupCallResponseV3
     */
    private $pickupCallResponse;

    /**
     * @param PackagesPickupCallResponseV3 $pickupCallResponseV3
     * @return PickupCallException
     */
    public static function fromPickupCallResponse(PackagesPickupCallResponseV3 $pickupCallResponseV3)
    {
        $e = new self(
            sprintf('PackagesPickupCallV3 return status "%s".', $pickupCallResponseV3->statusInfo()->status())
        );

        $e->pickupCallResponse = $pickupCallResponseV3;

        return $e;
    }

    /**
     * @return PackagesPickupCallResponseV3
     */
    public function pickupCallResponse()
    {
        return $this->pickupCallResponse;
    }
}