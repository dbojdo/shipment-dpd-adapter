<?php

namespace Webit\Shipment\DpdAdapter\Mapper\OpenUMLF;

use Webit\DPDClient\PackagesGeneration\OpenUMLF\PackageV2;
use Webit\DPDClient\PackagesGeneration\OpenUMLF\PayerType;
use Webit\Shipment\Address\DefaultSenderAddressProviderInterface;
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
     * @var DefaultSenderAddressProviderInterface
     */
    private $defaultSenderProvider;

    /**
     * PackageMapper constructor.
     * @param ReceiverMapper $receiverMapper
     * @param SenderMapper $senderMapper
     * @param ParcelMapper $parcelMapper
     * @param ServicesMapper $servicesMapper
     * @param DefaultSenderAddressProviderInterface $defaultSenderProvider
     */
    public function __construct(
        ReceiverMapper $receiverMapper,
        SenderMapper $senderMapper,
        ParcelMapper $parcelMapper,
        ServicesMapper $servicesMapper,
        DefaultSenderAddressProviderInterface $defaultSenderProvider
    ) {
        $this->receiverMapper = $receiverMapper;
        $this->senderMapper = $senderMapper;
        $this->parcelMapper = $parcelMapper;
        $this->servicesMapper = $servicesMapper;
        $this->defaultSenderProvider = $defaultSenderProvider;
    }

    /**
     * @param ConsignmentInterface $consignment
     * @return PackageV2
     */
    public function map(ConsignmentInterface $consignment)
    {
        $parcels = array();
        foreach ($consignment->getParcels() as $parcel) {
            $parcels[] = $this->parcelMapper->map($parcel);
        }

        $package = new PackageV2(
            $this->receiverMapper->map($consignment->getDeliveryAddress()),
            $this->senderMapper->map(
                $consignment->getSenderAddress() ?: $this->defaultSenderProvider->getSender()
            ),
            PayerType::sender(),
            $parcels,
            $this->servicesMapper->map($consignment),
            $reference = $this->randomise($consignment->getReference()),
            null,
            null,
            $consignment->getReference()
        );

        if ($reference) {
            $consignment->addVendorData('reference', $reference);
        }

        return $package;
    }

    private function randomise($reference)
    {
        if ($reference) {
            return sprintf(
                '%s (%s)',
                $reference,
                substr(md5(microtime().mt_rand(0, 999999)), mt_rand(0, 30), 2)
            );
        }

        return null;
    }
}
