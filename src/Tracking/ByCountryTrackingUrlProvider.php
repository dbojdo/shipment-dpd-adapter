<?php
/**
 * File ByCountryTrackingUrlProvider.php
 * Created at: 2017-09-10 18:02
 *
 * @author Daniel Bojdo <daniel.bojdo@web-it.eu>
 */

namespace Webit\Shipment\DpdAdapter\Tracking;

use Webit\Shipment\Consignment\ConsignmentInterface;

class ByCountryTrackingUrlProvider implements TrackingUrlProvider
{
    /**
     * @var TrackingUrlProvider[]
     */
    private $providers;

    /**
     * ByCountryTrackingUrlProvider constructor.
     * @param TrackingUrlProvider[] $providers
     */
    public function __construct(array $providers)
    {
        $this->providers = $providers;
    }

    /**
     * @inheritdoc
     */
    public function trackingUrl(ConsignmentInterface $consignment)
    {

        $country = $consignment->getDeliveryAddress()->getCountry();
        if ($this->providers[$country->getIsoCode()]) {
            return $this->providers[$country->getIsoCode()]->trackingUrl($consignment);
        }

        return '';
    }
}
