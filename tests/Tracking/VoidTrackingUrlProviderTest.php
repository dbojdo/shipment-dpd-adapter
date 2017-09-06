<?php
/**
 * File VoidTrackingUrlProviderTest.php
 * Created at: 2017-09-10 18:12
 *
 * @author Daniel Bojdo <daniel.bojdo@web-it.eu>
 */

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
