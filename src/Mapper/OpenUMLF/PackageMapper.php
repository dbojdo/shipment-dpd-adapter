<?php
/**
 * File PackageMapper.php
 * Created at: 2017-09-10 08:40
 *
 * @author Daniel Bojdo <daniel.bojdo@web-it.eu>
 */

namespace Webit\Shipment\DpdAdapter\Mapper\OpenUMLF;

use Webit\DPDClient\PackagesGeneration\OpenUMLF\Package;
use Webit\DPDClient\PackagesGeneration\OpenUMLF\PayerType;
use Webit\Shipment\Consignment\ConsignmentInterface;

class PackageMapper
{
    /**
     * @var ReceiverMapper
     */
    private $receiverMapper;

    /**
     * @var SenderMapper
     */
    private $senderMapper;

    /**
     * @var ParcelMapper
     */
    private $parcelMapper;

    /**
     * @var ServicesMapper
     */
    private $servicesMapper;

    /**
     * PackageMapper constructor.
     * @param ReceiverMapper $receiverMapper
     * @param SenderMapper $senderMapper
     * @param ParcelMapper $parcelMapper
     * @param ServicesMapper $servicesMapper
     */
    public function __construct(
        ReceiverMapper $receiverMapper,
        SenderMapper $senderMapper,
        ParcelMapper $parcelMapper,
        ServicesMapper $servicesMapper
    ) {
        $this->receiverMapper = $receiverMapper;
        $this->senderMapper = $senderMapper;
        $this->parcelMapper = $parcelMapper;
        $this->servicesMapper = $servicesMapper;
    }

    /**
     * @param ConsignmentInterface $consignment
     * @return Package
     */
    public function map(ConsignmentInterface $consignment)
    {
        $parcels = array();
        foreach ($consignment->getParcels() as $parcel) {
            $parcels[] = $this->parcelMapper->map($parcel);
        }

        $package = new Package(
            $this->receiverMapper->map($consignment->getDeliveryAddress()),
            $this->senderMapper->map($consignment->getSenderAddress()),
            PayerType::sender(),
            $parcels,
            $this->servicesMapper->map($consignment),
            $consignment->getReference()
        );

        return $package;
    }
}
