<?php

namespace Webit\Shipment\DpdAdapter\Mapper;

use Webit\DPDClient\PackagesGeneration\OpenUMLF\Services\Guarantee;
use Webit\Shipment\Vendor\VendorOption;

final class VendorOptions
{
    const DECLARED_VALUE_AMOUNT = 'service.declared_value_amount';
    const DECLARED_VALUE_CURRENCY = 'service.declared_value_currency';
    const GUARANTEE_TYPE = 'service.guarantee_type';
    const GUARANTEE_ATTR1 = 'service.guarantee_attr1';
    const CUD = 'service.cud';
    const DOX = 'service.dox';
    const ROD = 'service.rod';
    const COD_AMOUNT = 'service.cod_amount';
    const COD_CURRENCY = 'service.cod_currency';
    const SELF_COL = 'service.self_col';
    const IN_PERS = 'service.in_pers';
    const PRIV_PERS = 'service.priv_pers';
    const CARRY_IN = 'service.carry_in';
    const DUTY = 'service.duty';
    const PALLET = 'service.pallet';

    /**
     * @return array
     */
    public static function options()
    {
        $options = array(
            self::option(self::DECLARED_VALUE_AMOUNT),
            self::option(self::DECLARED_VALUE_CURRENCY),
            self::optionGuaranteeType(),
            self::option(self::GUARANTEE_ATTR1),
            self::option(self::GUARANTEE_ATTR1),
            self::booleanOption(self::CUD),
            self::booleanOption(self::DOX),
            self::booleanOption(self::ROD),
            self::option(self::COD_CURRENCY),
            self::option(self::SELF_COL),
            self::booleanOption(self::IN_PERS),
            self::booleanOption(self::PRIV_PERS),
            self::booleanOption(self::CARRY_IN),
            self::booleanOption(self::DUTY),
            self::booleanOption(self::PALLET),
        );

        return $options;
    }

    /**
     * @param string $code
     * @return VendorOption
     */
    private static function option($code)
    {
        $option = new VendorOption();
        $option->setCode($code);
        $option->setName($code);

        return $option;
    }

    private static function booleanOption($code)
    {
        $option = self::option($code);
        $option->addAllowedValue(false);
        $option->addAllowedValue(true);

        return $option;
    }

    /**
     * @return VendorOption
     */
    private static function optionGuaranteeType()
    {
        $option = self::option(self::GUARANTEE_TYPE);

        $option->addAllowedValue(Guarantee::SATURDAY);
        $option->addAllowedValue(Guarantee::TIME_0930);
        $option->addAllowedValue(Guarantee::TIME_1200);
        $option->addAllowedValue(Guarantee::TIMEFIXED);
        $option->addAllowedValue(Guarantee::INTER);
        $option->addAllowedValue(Guarantee::B2C);

        return $option;
    }
}