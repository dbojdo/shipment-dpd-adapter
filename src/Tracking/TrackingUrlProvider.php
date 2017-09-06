<?php

namespace Webit\Shipment\DpdAdapter\Tracking;

use Webit\Shipment\Consignment\ConsignmentInterface;

interface TrackingUrlProvider
{
    /**
     * @param ConsignmentInterface $consignment
     * @return string
     */
    public function trackingUrl(ConsignmentInterface $consignment);
}