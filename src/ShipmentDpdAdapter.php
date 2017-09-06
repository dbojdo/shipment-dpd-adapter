<?php

namespace Webit\Shipment\DpdAdapter;

use Doctrine\Common\Collections\ArrayCollection;
use Webit\DPDClient\Client;
use Webit\DPDClient\DPDPickupCallParams\DpdPickupCallParamsV3;
use Webit\DPDClient\DPDPickupCallParams\PickupCallOperationTypeDPPEnumV1;
use Webit\DPDClient\DPDPickupCallParams\PickupCallOrderTypeDPPEnumV1;
use Webit\DPDClient\DPDPickupCallParams\PickupCallSimplifiedDetailsDPPV1;
use Webit\DPDClient\DPDPickupCallParams\PickupCustomerDPPV1;
use Webit\DPDClient\DPDPickupCallParams\PickupPackagesParamsDPPV1;
use Webit\DPDClient\DPDPickupCallParams\PickupPayerDPPV1;
use Webit\DPDClient\DPDServicesParams\DPDServicesParamsV1;
use Webit\DPDClient\DPDServicesParams\PackageDSPV1;
use Webit\DPDClient\DPDServicesParams\PickupAddressDSPV1;
use Webit\DPDClient\DPDServicesParams\PolicyDSPEnumV1;
use Webit\DPDClient\DPDServicesParams\SessionDSPV1;
use Webit\DPDClient\PackagesGeneration\OpenUMLF\OpenUMLFV2;
use Webit\DPDClient\PackagesGeneration\PkgNumsGenerationPolicyEnumV1;
use Webit\Shipment\Consignment\ConsignmentInterface;
use Webit\Shipment\Consignment\DispatchConfirmationInterface;
use Webit\Shipment\DpdAdapter\Mapper\OpenUMLF\OpenUMLFMapper;
use Webit\Shipment\DpdAdapter\Mapper\PickupSenderProvider;
use Webit\Shipment\DpdAdapter\Tracking\TrackingUrlProvider;
use Webit\Shipment\DpdAdapter\Vendor\VendorFactory;
use Webit\Shipment\Manager\VendorAdapterInterface;
use Webit\Shipment\Parcel\ParcelInterface;
use Webit\Tools\Data\FilterCollection;
use Webit\Tools\Data\SorterCollection;

class ShipmentDpdAdapter implements VendorAdapterInterface
{
    /** @var VendorFactory */
    private $vendorFactory;

    /** @var TrackingUrlProvider */
    private $trackingUrlProvider;

    /** @var OpenUMLFMapper */
    private $openUmlfMapper;

    /** @var PickupSenderProvider */
    private $pickupSenderProvider;

    /** @var Client */
    private $client;

    /** @var int */
    private $fid;

    /** @var string */
    private $language;

    /**
     * ShipmentDpdAdapter constructor.
     * @param VendorFactory $vendorFactory
     * @param TrackingUrlProvider $trackingUrlProvider
     * @param OpenUMLFMapper $openUmlfMapper
     * @param PickupSenderProvider $pickupSenderProvider
     * @param Client $client
     * @param int $fid
     * @param string $language
     */
    public function __construct(
        VendorFactory $vendorFactory,
        TrackingUrlProvider $trackingUrlProvider,
        OpenUMLFMapper $openUmlfMapper,
        PickupSenderProvider $pickupSenderProvider,
        Client $client,
        $fid,
        $language
    ) {
        $this->vendorFactory = $vendorFactory;
        $this->trackingUrlProvider = $trackingUrlProvider;
        $this->openUmlfMapper = $openUmlfMapper;
        $this->pickupSenderProvider = $pickupSenderProvider;
        $this->client = $client;
        $this->fid = $fid;
        $this->language = $language;
    }

    /**
     * Returns consignments
     * @param FilterCollection $filters
     * @param SorterCollection $sorters
     * @param int $limit
     * @param int $offset
     * @return ArrayCollection
     */
    public function getConsignments(
        FilterCollection $filters = null,
        SorterCollection $sorters = null,
        $limit = 50,
        $offset = 0
    ) {
        return new ArrayCollection();
    }

    /**
     * @inheritdoc
     */
    public function dispatch(DispatchConfirmationInterface $dispatchConfirmation)
    {
        $openUMLF = $this->openUmlfMapper->mapDispatchConfirmation($dispatchConfirmation);
        $response = $this->client->generatePackagesNumbersV3(
            $openUMLF,
            PkgNumsGenerationPolicyEnumV1::allOrNothing(),
            $this->language
        );

        if ($response->status() != 'OK') {
            throw PackageGenerationException::fromPackageGenerationResponse($response);
        }

        $sessionId = $response->sessionId();
        foreach ($response->packages() as $i => $package) {
            /** @var ConsignmentInterface $consignment */
            $consignment = $dispatchConfirmation->getConsignments()->get($i);
            $consignment->setVendorId($package->packageId());
            $consignment->addVendorData('sessionId', $sessionId);

            foreach ($package->parcels() as $j => $parcel) {
                /** @var ParcelInterface $cParcel */
                $cParcel = $consignment->getParcels()->get($j);
                $cParcel->setNumber($parcel->waybill());
            }
        }

        $protocolId = $this->protocol($sessionId);

        $dispatchConfirmation->setNumber($protocolId);
        $dispatchConfirmation->setDispatchedAt(new \DateTime());
        $dispatchConfirmation->addVendorData('sessionId', $sessionId);
        $dispatchConfirmation->addVendorData('protocolId', $protocolId);

        if ($dispatchConfirmation->isCourierCalled()) {
            $pickupCallParams = new DpdPickupCallParamsV3(
                PickupCallOperationTypeDPPEnumV1::insert(),
                null,
                null,
                null,
                $dispatchConfirmation->getPickUpAt(),
                null,
                null,
                PickupCallOrderTypeDPPEnumV1::domestic(),
                true,
                new PickupCallSimplifiedDetailsDPPV1(
                    new PickupPayerDPPV1($this->fid, 'x'),
                    new PickupCustomerDPPV1(),
                    $this->pickupSenderProvider->getPickupSender(),
                    $this->mapPickupPackagesParams($openUMLF)
                )
            );

            $pickupCall = $this->client->packagesPickupCallV3($pickupCallParams);

            if ($pickupCall->statusInfo()->status() != 'OK') {
                throw PickupCallException::fromPickupCallResponse($pickupCall);
            }

            $dispatchConfirmation->addVendorData('orderNumber', $pickupCall->orderNumber());
        }
    }

    /**
     * @inheritdoc
     */
    public function synchronizeConsignment(ConsignmentInterface $consignment)
    {
        // nothing to do here
    }

    /**
     * @inheritdoc
     */
    public function synchronizeParcelStatus(ParcelInterface $parcel)
    {
        // TODO: Implement synchronizeParcelStatus() method.
    }

    /**
     * @inheritdoc
     */
    public function saveConsignment(ConsignmentInterface $consignment)
    {
        // nothing to do here
    }

    /**
     * @inheritdoc
     */
    public function removeConsignment(ConsignmentInterface $consignment)
    {
        // nothing to do here
    }

    /**
     * @inheritdoc
     */
    public function cancelConsignment(ConsignmentInterface $consignment)
    {
        // nothing to do here
    }

    /**
     * @inheritdoc
     */
    public function getConsignmentLabel(ConsignmentInterface $consignment, $mode = null)
    {
        $params = $this->serviceParams(null, array($consignment->getVendorId()));

        $response = $this->client->generateSpedLabelsV2($params);

        return $response->documentData();
    }

    /**
     * @inheritdoc
     */
    public function getConsignmentDispatchConfirmationLabel(
        DispatchConfirmationInterface $dispatchConfirmation,
        $mode = null
    ) {
        $sessionId = $this->sessionIdOfDispatchConfirmation($dispatchConfirmation);
        $params = $this->serviceParams($sessionId);

        $response = $this->client->generateSpedLabelsV2($params);

        return $response->documentData();
    }

    /**
     * @inheritdoc
     */
    public function getConsignmentDispatchConfirmationReceipt(
        DispatchConfirmationInterface $dispatchConfirmation,
        $mode = null
    ) {
        $sessionId = $this->sessionIdOfDispatchConfirmation($dispatchConfirmation);
        $params = $this->serviceParams($sessionId);

        $response = $this->client->generateProtocolV1($params);

        return $response->documentData();
    }

    /**
     * @inheritdoc
     */
    public function getConsignmentTrackingUrl(ConsignmentInterface $consignment)
    {
        $consignment->getDeliveryAddress()->getCountry()->getIsoCode();
        return $this->trackingUrlProvider->trackingUrl($consignment);
    }

    /**
     * @inheritdoc
     */
    public function createVendor()
    {
        return $this->vendorFactory->create();
    }

    /**
     * @param int $sessionId
     * @return string
     */
    private function protocol($sessionId)
    {
        $protocolResponse = $this->client->generateProtocolV1(
            $this->serviceParams($sessionId)
        );

        return $protocolResponse->documentId();
    }

    /**
     * @param $sessionId
     * @param array $packageIds
     * @param int $documentId
     * @return DPDServicesParamsV1
     */
    private function serviceParams($sessionId = null, array $packageIds = array(), $documentId = null)
    {
        $packages = array();
        foreach ($packageIds as $id) {
            $packages[] = new PackageDSPV1($id);
        }

        $session = $packages ? SessionDSPV1::fromPackages($packages) : SessionDSPV1::fromSession($sessionId);
        return new DPDServicesParamsV1(
            PolicyDSPEnumV1::stopOnFirstError(),
            $session,
            PickupAddressDSPV1::fromFid($this->fid),
            $documentId
        );
    }

    private function sessionIdOfDispatchConfirmation(DispatchConfirmationInterface $dispatchConfirmation)
    {
        $data = (array)$dispatchConfirmation->getVendorData();
        return isset($data['sessionId']) ? $data['sessionId'] : null;
    }

    /**
     * @param OpenUMLFV2 $openUMLF
     * @return PickupPackagesParamsDPPV1
     */
    private function mapPickupPackagesParams(OpenUMLFV2 $openUMLF)
    {
        $doxNo = 0;
        $parcelsNo = $openUMLF->packages();
        $palletNo = 0;
        foreach ($openUMLF->packages() as $package) {
            $parcels = $package->parcels();
            $parcelsNo += count($parcels);
            $palletNo = $package->services()->pallet() ? ++$palletNo : $palletNo;
            $doxNo = $package->services()->dox() ? ++$doxNo : $doxNo;
        }

        return new PickupPackagesParamsDPPV1(
            $doxNo > 0,
            $parcelsNo > $palletNo,
            $palletNo > 0,
            $parcelsNo,
            $palletNo,
            $doxNo
        );
    }
}
