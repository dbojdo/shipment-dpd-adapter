<?php
/**
 * File OpenUMLFMapperTest.php
 * Created at: 2017-09-10 17:31
 *
 * @author Daniel Bojdo <daniel.bojdo@web-it.eu>
 */

namespace Webit\Shipment\DpdAdapter\Mapper\OpenUMLF;

use Doctrine\Common\Collections\ArrayCollection;
use Webit\DPDClient\PackagesGeneration\OpenUMLF\OpenUMLF;
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
            $this->prophesize('Webit\DPDClient\PackagesGeneration\OpenUMLF\Package')->reveal(),
            $this->prophesize('Webit\DPDClient\PackagesGeneration\OpenUMLF\Package')->reveal()
        );

        $this->packageMapper->map($consignments->get(0))->willReturn($packages[0]);
        $this->packageMapper->map($consignments->get(1))->willReturn($packages[1]);

        $dispatchConfirmation->getConsignments()->willReturn($consignments);
        $this->assertEquals(
            new OpenUMLF($packages),
            $this->openUmlfMapper->mapDispatchConfirmation($dispatchConfirmation->reveal())
        );
    }
}
