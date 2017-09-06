<?php
/**
 * File OpenUMLFMapper.php
 * Created at: 2017-09-10 08:47
 *
 * @author Daniel Bojdo <daniel.bojdo@web-it.eu>
 */

namespace Webit\Shipment\DpdAdapter\Mapper\OpenUMLF;

use Webit\DPDClient\PackagesGeneration\OpenUMLF\OpenUMLF;
use Webit\DPDClient\PackagesGeneration\OpenUMLF\OpenUMLFV2;
use Webit\Shipment\Consignment\DispatchConfirmationInterface;

class OpenUMLFMapper
{
    /**
     * @var PackageMapper
     */
    private $packageMapper;

    /**
     * OpenUMLFMapper constructor.
     * @param PackageMapper $packageMapper
     */
    public function __construct(PackageMapper $packageMapper)
    {
        $this->packageMapper = $packageMapper;
    }

    /**
     * @param DispatchConfirmationInterface $dispatchConfirmation
     * @return OpenUMLFV2
     */
    public function mapDispatchConfirmation(DispatchConfirmationInterface $dispatchConfirmation)
    {
        $packages = array();
        foreach ($dispatchConfirmation->getConsignments() as $consignment) {
            $packages[] = $this->packageMapper->map($consignment);
        }

        return new OpenUMLFV2($packages);
    }
}
