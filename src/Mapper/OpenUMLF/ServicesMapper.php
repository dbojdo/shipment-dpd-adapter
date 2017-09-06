<?php

namespace Webit\Shipment\DpdAdapter\Mapper\OpenUMLF;

use Webit\DPDClient\PackagesGeneration\OpenUMLF\Services;
use Webit\Shipment\Consignment\ConsignmentInterface;
use Webit\Shipment\DpdAdapter\Mapper\VendorOptions;
use Webit\Shipment\Vendor\VendorOptionValueCollection;

class ServicesMapper
{
    /**
     * @var string
     */
    private $defaultCurrency;

    /**
     * ServicesMapper constructor.
     * @param string $defaultCurrency
     */
    public function __construct($defaultCurrency)
    {
        $this->defaultCurrency = $defaultCurrency;
    }

    /**
     * @param ConsignmentInterface $consignment
     * @return Services
     */
    public function map(ConsignmentInterface $consignment)
    {
        $vendorOptionValues = $consignment->getVendorOptions();
        return new Services(
            $this->mapDeclaredValue($vendorOptionValues),
            $this->mapGuarantee($vendorOptionValues),
            $this->mapBoolean($vendorOptionValues, VendorOptions::CUD),
            $this->mapBoolean($vendorOptionValues, VendorOptions::DOX),
            $this->mapBoolean($vendorOptionValues, VendorOptions::ROD),
            $this->mapCod($consignment, $vendorOptionValues),
            $this->mapBoolean($vendorOptionValues, VendorOptions::IN_PERS),
            $this->mapSelfCol($vendorOptionValues),
            $this->mapBoolean($vendorOptionValues, VendorOptions::PRIV_PERS),
            $this->mapBoolean($vendorOptionValues, VendorOptions::CARRY_IN),
            $this->mapBoolean($vendorOptionValues, VendorOptions::DUTY),
            $this->mapBoolean($vendorOptionValues, VendorOptions::PALLET)
        );
    }

    /**
     * @param VendorOptionValueCollection $vendorOptionValues
     * @return null|Services\DeclaredValue
     */
    private function mapDeclaredValue(VendorOptionValueCollection $vendorOptionValues)
    {
        $amount = $vendorOptionValues->getValue(VendorOptions::DECLARED_VALUE_AMOUNT);
        $amount = $amount ? $amount->getValue() : null;

        $currency = $vendorOptionValues->getValue(VendorOptions::DECLARED_VALUE_CURRENCY);
        $currency = $currency ? $currency->getValue() : $this->defaultCurrency;

        if ($amount && $currency) {
            return new Services\DeclaredValue($amount, $currency);
        }

        return null;
    }

    /**
     * @param VendorOptionValueCollection $vendorOptionValues
     * @return null|Services\Guarantee
     */
    private function mapGuarantee(VendorOptionValueCollection $vendorOptionValues)
    {
        $type = $vendorOptionValues->getValue(VendorOptions::GUARANTEE_TYPE);
        $type = $type ? $type->getValue() : null;

        $attr1 = $vendorOptionValues->getValue(VendorOptions::GUARANTEE_ATTR1);
        $attr1 = $attr1 ? $attr1->getValue() : null;

        switch ($type) {
            case Services\Guarantee::TIMEFIXED:
                return $attr1 ? Services\Guarantee::timeFixed($attr1) : null;
            case Services\Guarantee::B2C:
                return $attr1 ? Services\Guarantee::b2c($attr1) : null;
            case Services\Guarantee::SATURDAY:
                return Services\Guarantee::saturday();
            case Services\Guarantee::TIME_0930:
                return Services\Guarantee::time0930();
            case Services\Guarantee::TIME_1200:
                return Services\Guarantee::time1200();
            case Services\Guarantee::INTER:
                return Services\Guarantee::inter();
        }

        return null;
    }

    /**
     * @param VendorOptionValueCollection $vendorOptionValues
     * @param string $service
     * @return bool
     */
    private function mapBoolean(VendorOptionValueCollection $vendorOptionValues, $service)
    {
        $value = $vendorOptionValues->getValue($service);
        return $value ? (bool)$value->getValue() : false;
    }

    /**
     * @param VendorOptionValueCollection $vendorOptionValues
     * @return null|Services\SelfCol
     */
    private function mapSelfCol(VendorOptionValueCollection $vendorOptionValues)
    {
        $value = $vendorOptionValues->getValue(VendorOptions::SELF_COL);

        $value = $value ? $value->getValue() : null;
        switch ($value) {
            case Services\SelfCol::COMP:
                return Services\SelfCol::comp();
            case Services\SelfCol::PRIV:
                return Services\SelfCol::priv();
        }

        return null;
    }

    /**
     * @param ConsignmentInterface $consignment
     * @param VendorOptionValueCollection $vendorOptionValues
     * @return null|Services\Cod
     */
    private function mapCod(ConsignmentInterface $consignment, VendorOptionValueCollection $vendorOptionValues)
    {
        if (! $consignment->isCod()) {
            return null;
        }

        $amount = $consignment->getCodAmount();
        $currency = $vendorOptionValues->getValue(VendorOptions::COD_CURRENCY);
        $currency = $currency ? $currency->getValue() : $this->defaultCurrency;

        if ($amount && $currency) {
            return new Services\Cod($amount, $currency);
        }

        return null;
    }
}