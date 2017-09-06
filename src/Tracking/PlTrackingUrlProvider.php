<?php
/**
 * File PlTrackingUrlProvider.php
 * Created at: 2017-09-10 18:04
 *
 * @author Daniel Bojdo <daniel.bojdo@web-it.eu>
 */

namespace Webit\Shipment\DpdAdapter\Tracking;

use Webit\Shipment\Consignment\ConsignmentInterface;

class PlTrackingUrlProvider implements TrackingUrlProvider
{
    const BASE_URL = 'https://tracktrace.dpd.com.pl/parcelDetails?typ=1';

    /**
     * @inheritdoc
     */
    public function trackingUrl(ConsignmentInterface $consignment)
    {
        $waybills = array();
        foreach ($consignment->getParcels() as $parcel) {
            if ($waybill = $parcel->getNumber()) {
                $waybills[] = $waybill;
            }
        }

        if (!$waybills) {
            return '';
        }

        $params = array_replace(
            array_fill(0, 10, ''),
            $waybills
        );

        array_unshift(
            $params,
            self::BASE_URL.'&p1=%s&p2=%s&p3=%s&p4=%s&p5=%s&p6=%s&p7=%s&p8=%s&p9=%s&p10=%s'
        );

        return call_user_func_array('sprintf', $params);
    }
}