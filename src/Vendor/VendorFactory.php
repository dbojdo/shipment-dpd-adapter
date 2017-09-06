<?php
/**
 * File VendorFactory.php
 * Created at: 2017-09-07 21:05
 *
 * @author Daniel Bojdo <daniel.bojdo@web-it.eu>
 */

namespace Webit\Shipment\DpdAdapter\Vendor;

use Webit\Shipment\DpdAdapter\Mapper\PrintModes;
use Webit\Shipment\DpdAdapter\Mapper\VendorOptions;
use Webit\Shipment\Vendor\Vendor;

class VendorFactory
{
    private $vendorClass;

    /**
     * VendorFactory constructor.
     * @param $vendorClass
     */
    public function __construct($vendorClass)
    {
        $this->vendorClass = $vendorClass;
    }

    /**
     * @return Vendor
     */
    public function create()
    {
        $vendorClass = $this->vendorClass;

        /** @var Vendor $vendor */
        $vendor = new $vendorClass();

        $vendor->setName('DPD');
        $vendor->setActive(true);
        $vendor->setDescription('DPD');

        foreach (VendorOptions::options() as $option) {
            $vendor->getConsignmentOptions()->addOption($option);
            $vendor->getParcelOptions()->addOption($option);
        }

        foreach (PrintModes::modes() as $mode) {
            $vendor->getLabelPrintModes()->add($mode);
            $vendor->getDispatchConfirmationPrintModes()->add($mode);
        }

        return $vendor;
    }
}
