<?php
/**
 * File PrintModesTest.php
 * Created at: 2017-09-10 16:07
 *
 * @author Daniel Bojdo <daniel.bojdo@web-it.eu>
 */

namespace Webit\Shipment\DpdAdapter\Mapper;

use Webit\Shipment\DpdAdapter\AbstractTest;

class PrintModesTest extends AbstractTest
{
    /**
     * @test
     */
    public function shouldReturnPrintModes()
    {
        $modes = PrintModes::modes();
        foreach ($modes as $mode) {
            $this->assertInternalType('string', $mode);
        }
    }
}
