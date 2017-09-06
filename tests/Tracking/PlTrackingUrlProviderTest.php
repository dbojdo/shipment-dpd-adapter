<?php

namespace Webit\Shipment\DpdAdapter\Tracking;

use Doctrine\Common\Collections\ArrayCollection;
use Webit\Shipment\Consignment\ConsignmentInterface;
use Webit\Shipment\DpdAdapter\AbstractTest;

class PlTrackingUrlProviderTest extends AbstractTest
{
    /**
     * @test
     * @dataProvider tracking
     * @param ConsignmentInterface $consignment
     * @param $expectedUrl
     */
    public function shouldReturnTrackingUrl(ConsignmentInterface $consignment, $expectedUrl)
    {
        $provider = new PlTrackingUrlProvider();

        $this->assertEquals($expectedUrl, $provider->trackingUrl($consignment));
    }

    public function tracking()
    {
        return array(
            'empty waybills' => array(
                $this->consignment(array()),
                ''
            ),
            '2 waybills' => array(
                $this->consignment($waybills = array(
                    $this->randomPositiveInt(9999999),
                    $this->randomPositiveInt(9999999)
                )),
                $this->trackingUrl($waybills)
            ),
            'more than 10 waybills' => array(
                $this->consignment($waybills = array(
                    $this->randomPositiveInt(9999999),
                    $this->randomPositiveInt(9999999),
                    $this->randomPositiveInt(9999999),
                    $this->randomPositiveInt(9999999),
                    $this->randomPositiveInt(9999999),
                    $this->randomPositiveInt(9999999),
                    $this->randomPositiveInt(9999999),
                    $this->randomPositiveInt(9999999),
                    $this->randomPositiveInt(9999999),
                    $this->randomPositiveInt(9999999),
                    $this->randomPositiveInt(9999999),
                    $this->randomPositiveInt(9999999),
                    $this->randomPositiveInt(9999999)
                )),
                $this->trackingUrl($waybills)
            ),
        );
    }

    private function trackingUrl(array $waybills)
    {
        $params = array_replace(
            array_fill(0, 10, ''),
            $waybills
        );
        $params = array_slice($params, 0, 10);

        array_unshift($params, PlTrackingUrlProvider::BASE_URL.'&p1=%s&p2=%s&p3=%s&p4=%s&p5=%s&p6=%s&p7=%s&p8=%s&p9=%s&p10=%s');

        return call_user_func_array('sprintf', $params);
    }

    private function consignment(array $waybills)
    {
        $consignment = $this->prophesize('Webit\Shipment\Consignment\ConsignmentInterface');

        $parcels = array();
        foreach ($waybills as $waybill) {
            $parcel = $this->prophesize('Webit\Shipment\Parcel\ParcelInterface');
            $parcel->getNumber()->willReturn($waybill);
            $parcels[] = $parcel->reveal();
        }

        $consignment->getParcels()->willReturn(new ArrayCollection($parcels));

        return $consignment->reveal();
    }
}
