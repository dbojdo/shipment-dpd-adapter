<?php

namespace Webit\Shipment\DpdAdapter\Tracking;

use Webit\Addressing\Model\Country;
use Webit\Shipment\DpdAdapter\AbstractTest;

class ByCountryTrackingUrlProviderTest extends AbstractTest
{
    /**
     * @test
     */
    public function shouldGenerateUrlByDeliveryAddressCountry()
    {
        $country = $this->faker()->countryCode;
        $innerCountryProvider = $this->prophesize('Webit\Shipment\DpdAdapter\Tracking\TrackingUrlProvider');

        $provider = new ByCountryTrackingUrlProvider(
            array($country => $innerCountryProvider->reveal())
        );

        $consignment = $this->consignmentToCountry($country);

        $expectedUrl = $this->faker()->url;

        $innerCountryProvider->trackingUrl($consignment)->willReturn($expectedUrl);

        $this->assertEquals($expectedUrl, $provider->trackingUrl($consignment));
    }

    private function consignmentToCountry($country)
    {
        $consignment = $this->prophesize('Webit\Shipment\Consignment\ConsignmentInterface');

        $deliveryAddress = $this->prophesize('Webit\Shipment\Address\DeliveryAddressInterface');
        $deliveryAddress->getCountry()->willReturn(new Country($this->randomString(), $country));

        $consignment->getDeliveryAddress()->willReturn($deliveryAddress->reveal());

        return $consignment->reveal();
    }
}
