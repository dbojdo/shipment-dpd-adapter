<?php

namespace Webit\Shipment\DpdAdapter\Tracking;

use Webit\Shipment\Consignment\ConsignmentInterface;

class VoidTrackingUrlProvider implements TrackingUrlProvider
{

    /**
     * @inheritdoc
     */
    public function trackingUrl(ConsignmentInterface $consignment)
    {
        return '';
    }
}