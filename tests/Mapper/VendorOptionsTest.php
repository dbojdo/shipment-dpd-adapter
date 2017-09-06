<?php
/**
 * File VendorOptionsTest.php
 * Created at: 2017-09-10 16:03
 *
 * @author Daniel Bojdo <daniel.bojdo@web-it.eu>
 */

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