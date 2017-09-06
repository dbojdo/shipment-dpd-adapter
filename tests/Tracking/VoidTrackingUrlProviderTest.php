<?php

namespace Webit\Shipment\DpdAdapter\Tracking;

use Webit\Shipment\DpdAdapter\AbstractTest;

class VoidTrackingUrlProviderTest extends AbstractTest
{
    /**
     * @test
     */
    public function shouldReturnEmptyString()
    {
        $provider = new VoidTrackingUrlProvider();

        $consignment = $this->prophesize('Webit\Shipment\Consignment\ConsignmentInterface')->reveal();
        $this->assertEquals('', $provider->trackingUrl($consignment));
    }
}
