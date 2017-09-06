<?php

namespace Webit\Shipment\DpdAdapter\Mapper\OpenUMLF;

use Doctrine\Common\Collections\ArrayCollection;
use Webit\DPDClient\PackagesGeneration\OpenUMLF\OpenUMLF;
use Webit\DPDClient\PackagesGeneration\OpenUMLF\OpenUMLFV2;
use Webit\Shipment\DpdAdapter\AbstractTest;

class OpenUMLFMapperTest extends AbstractTest
{
    /**
     * @var PackageMapper
     */
    private $packageMapper;

    /**
     * @var OpenUMLFMapper
     */
    private $openUmlfMapper;

    protected function setUp()
    {
        $this->packageMapper = $this->prophesize('Webit\Shipment\DpdAdapter\Mapper\OpenUMLF\PackageMapper');
        $this->openUmlfMapper = new OpenUMLFMapper($this->packageMapper->reveal());
    }

    /**
     * @test
     */
    public function shouldMapOpenUMLF()
    {
        $dispatchConfirmation = $this->prophesize('Webit\Shipment\Consignment\DispatchConfirmationInterface');

        $consignments = new ArrayCollection(
            array(
                $this->prophesize('Webit\Shipment\Consignment\ConsignmentInterface')->reveal(),
                $this->prophesize('Webit\Shipment\Consignment\ConsignmentInterface')->reveal()
            )
        );

        $packages = array(
            $this->prophesize('Webit\DPDClient\PackagesGeneration\OpenUMLF\PackageV2')->reveal(),
            $this->prophesize('Webit\DPDClient\PackagesGeneration\OpenUMLF\PackageV2')->reveal()
        );

        $this->packageMapper->map($consignments->get(0))->willReturn($packages[0]);
        $this->packageMapper->map($consignments->get(1))->willReturn($packages[1]);

        $dispatchConfirmation->getConsignments()->willReturn($consignments);
        $this->assertEquals(
            new OpenUMLFV2($packages),
            $this->openUmlfMapper->mapDispatchConfirmation($dispatchConfirmation->reveal())
        );
    }
}
