<?php
/**
 * File VoidTrackingUrlProvider.php
 * Created at: 2017-09-10 18:12
 *
 * @author Daniel Bojdo <daniel.bojdo@web-it.eu>
 */

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