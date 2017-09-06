<?php
/**
 * File ShipmentDpdAdapter.php
 * Created at: 2017-06-08 19:55
 *
 * @author Daniel Bojdo <daniel.bojdo@web-it.eu>
 */

namespace Webit\Shipment\DpdAdapter;

use Doctrine\Common\Collections\ArrayCollection;
use Webit\DPDClient\Client;
use Webit\DPDClient\DocumentGeneration\DocumentGenerationResponseV1;
use Webit\DPDClient\DPDPickupCallParams\ContactInfoDPPV1;
use Webit\DPDClient\DPDPickupCallParams\DpdPickupCallParamsV1;
use Webit\DPDClient\DPDPickupCallParams\PolicyDPPEnumV1;
use Webit\DPDClient\DPDPickupCallParams\ProtocolDPPV1;
use Webit\DPDClient\DPDServicesParams\DPDServicesParamsV1;
use Webit\DPDClient\DPDServicesParams\PackageDSPV1;
use Webit\DPDClient\DPDServicesParams\PickupAddressDSPV1;
use Webit\DPDClient\DPDServicesParams\PolicyDSPEnumV1;
use Webit\DPDClient\DPDServicesParams\SessionDSPV1;
use Webit\DPDClient\PackagesGeneration\PkgNumsGenerationPolicyEnumV1;
use Webit\Shipment\Consignment\ConsignmentInterface;
use Webit\Shipment\Consignment\DispatchConfirmationInterface;
use Webit\Shipment\DpdAdapter\Mapper\OpenUMLF\OpenUMLFMapper;
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

    /**
     * @var TrackingUrlProvider
     */
    private $trackingUrlProvider;

    /** @var OpenUMLFMapper */
    private $openUmlfMapper;

    /** @var Client */
    private $client;

    /** @var int */
    private $fid;

    /**
     * ShipmentDpdAdapter constructor.
     * @param VendorFactory $vendorFactory
     * @param TrackingUrlProvider $trackingUrlProvider
     * @param OpenUMLFMapper $openUmlfMapper
     * @param Client $client
     * @param int $fid
     */
    public function __construct(
        VendorFactory $vendorFactory,
        TrackingUrlProvider $trackingUrlProvider,
        OpenUMLFMapper $openUmlfMapper,
        Client $client,
        $fid
    ) {
        $this->vendorFactory = $vendorFactory;
        $this->trackingUrlProvider = $trackingUrlProvider;
        $this->openUmlfMapper = $openUmlfMapper;
        $this->client = $client;
        $this->fid = $fid;
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
        $response = $this->client->generatePackagesNumbersV1(
            $this->openUmlfMapper->mapDispatchConfirmation($dispatchConfirmation),
            PkgNumsGenerationPolicyEnumV1::allOrNothing()
        );

        $sessionId = $response->sessionId();
        foreach ($response->packages() as $i => $package) {
            /** @var ConsignmentInterface $consignment */
            $consignment = $dispatchConfirmation->getConsignments()->get($i);
            $consignment->setVendorId($package->packageId());
            $consignment->setVendorData(array('sessionId' => $sessionId));

            foreach ($package->parcels() as $j => $parcel) {
                /** @var ParcelInterface $cParcel */
                $cParcel = $consignment->getParcels()->get($j);
                $cParcel->setNumber($parcel->waybill());
            }
        }

        $protocolId = $this->protocol($sessionId);
        $protocol = new ProtocolDPPV1($protocolId);

        $pickupCallParams = new DpdPickupCallParamsV1(
            PolicyDPPEnumV1::stopOnFirstError(),
            PickupAddressDSPV1::fromFid($this->fid),
            new ContactInfoDPPV1(),
            array(
                $protocol
            ),
            $dispatchConfirmation->getPickUpAt()
        );

        $pickupCall = $this->client->packagesPickupCallV1($pickupCallParams);
        $dispatchConfirmation->setNumber($pickupCall->orderNumber());

        $dispatchConfirmation->setVendorData(array(
            'sessionId' => $sessionId,
            'protocolId' => $protocolId
        ));

        $dispatchConfirmation->setDispatchedAt(new \DateTime());
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
        $data = (array)$consignment->getVendorData();
        $sessionId = isset($data['sessionId']) ? $data['sessionId'] : null;

        $params = $this->serviceParams($sessionId, array($consignment->getVendorId()));

        $response = $this->client->generateSpedLabelsV1($params);

        return $this->saveAsFile($response, 'labels');
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

        $response = $this->client->generateSpedLabelsV1($params);

        return $this->saveAsFile($response, 'labels');
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

        return $this->saveAsFile($response, 'receipt');
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
    private function serviceParams($sessionId, array $packageIds = array(), $documentId = null)
    {
        $packages = array();
        foreach ($packageIds as $id) {
            $packages[] = new PackageDSPV1($id);
        }

        return new DPDServicesParamsV1(
            PolicyDSPEnumV1::stopOnFirstError(),
            SessionDSPV1::domestic($sessionId, $packages),
            PickupAddressDSPV1::fromFid($this->fid),
            $documentId
        );
    }

    /**
     * @param DocumentGenerationResponseV1 $response
     * @param string $prefix
     * @return \SplFileInfo
     */
    private function saveAsFile(DocumentGenerationResponseV1 $response, $prefix)
    {
        $filename = tempnam(sys_get_temp_dir(), $prefix);
        file_put_contents($filename, $response->documentData());

        return new \SplFileInfo($filename);
    }

    private function sessionIdOfDispatchConfirmation(DispatchConfirmationInterface $dispatchConfirmation)
    {
        $data = (array)$dispatchConfirmation->getVendorData();
        return isset($data['sessionId']) ? $data['sessionId'] : null;
    }
}
