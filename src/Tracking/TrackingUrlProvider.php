<?php
/**
 * File TrackingUrlProvider.php
 * Created at: 2017-09-10 17:59
 *
 * @author Daniel Bojdo <daniel.bojdo@web-it.eu>
 */

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