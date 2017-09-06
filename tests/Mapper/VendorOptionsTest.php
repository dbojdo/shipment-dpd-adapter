<?php

namespace Webit\Shipment\DpdAdapter\Mapper;

use Webit\Shipment\DpdAdapter\AbstractTest;

class VendorOptionsTest extends AbstractTest
{
    /**
     * @test
     */
    public function shouldReturnOptions()
    {
        $options = VendorOptions::options();

        foreach ($options as $option) {
            $this->assertInstanceOf('Webit\Shipment\Vendor\VendorOption', $option);
        }
    }
}