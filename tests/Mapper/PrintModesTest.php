<?php

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
