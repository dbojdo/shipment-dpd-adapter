<?php

namespace Webit\Shipment\DpdAdapter\Mapper\OpenUMLF;

use Webit\DPDClient\DPDServices\PackagesGeneration\OpenUMLF\Services;
use Webit\Shipment\DpdAdapter\AbstractTest;
use Webit\Shipment\DpdAdapter\Mapper\VendorOptions;
use Webit\Shipment\Vendor\VendorOptionValue;
use Webit\Shipment\Vendor\VendorOptionValueCollection;

class ServicesMapperTest extends AbstractTest
{
    /**
     * @test
     * @param VendorOptionValueCollection $valueCollection
     * @param float $codAmount
     * @param Services $expectedServices
     * @internal param ConsignmentInterface $consignment
     * @dataProvider options
     */
    public function shouldMapConsignmentToDpdServices(
        VendorOptionValueCollection $valueCollection,
        $codAmount,
        Services $expectedServices
    ) {
        $consignment = $this->prophesize('Webit\Shipment\Consignment\ConsignmentInterface');
        $consignment->getVendorOptions()->willReturn($valueCollection);
        $consignment->isCod()->willReturn($codAmount > 0);
        if ($codAmount) {
            $consignment->getCodAmount()->willReturn($codAmount);
        }

        $consignment = $consignment->reveal();

        $mapper = new ServicesMapper($this->faker()->currencyCode);
        $services = $mapper->map($consignment);
        $this->assertEquals($expectedServices, $services);
    }

    public function options()
    {
        return array(
            'no services' => array(
                $this->optionValueCollection(),
                0,
                new Services()
            ),
            'boolean services' => array(
                $this->optionValueCollection(
                    null,
                    null,
                    null,
                    null,
                    true,
                    true,
                    true,
                    null,
                    true,
                    null,
                    true,
                    true,
                    true,
                    true
                ),
                0,
                new Services(
                    null,
                    null,
                    true,
                    true,
                    true,
                    null,
                    true,
                    null,
                    true,
                    true,
                    true,
                    true
                )
            ),
            'declared amount - valid' => array(
                $this->optionValueCollection(
                    $declaredValueAmount = $this->randomPositiveInt() / 100,
                    $declaredValueCurrency = 'EUR'
                ),
                0,
                new Services(
                    Services\DeclaredValue::create($declaredValueAmount, $declaredValueCurrency)
                )
            ),
            'declared amount - amount not set' => array(
                $this->optionValueCollection(
                    null,
                    $declaredValueCurrency = 'EUR'
                ),
                0,
                new Services()
            ),
            'guarantee (saturaday)' => array(
                $this->optionValueCollection(
                    null, null, Services\Guarantee::SATURDAY
                ),
                0,
                new Services(null, Services\Guarantee::saturday())
            ),
            'guarantee (9:30)' => array(
                $this->optionValueCollection(
                    null, null, Services\Guarantee::TIME_0930
                ),
                0,
                new Services(null, Services\Guarantee::time0930())
            ),
            'guarantee (12:00)' => array(
                $this->optionValueCollection(
                    null, null, Services\Guarantee::TIME_1200
                ),
                0,
                new Services(null, Services\Guarantee::time1200())
            ),
            'guarantee (inter)' => array(
                $this->optionValueCollection(
                    null, null, Services\Guarantee::INTER
                ),
                0,
                new Services(null, Services\Guarantee::inter())
            ),
            'guarantee (b2c)' => array(
                $this->optionValueCollection(
                    null, null, Services\Guarantee::B2C, $attr1 = '12:00-16:00'
                ),
                0,
                new Services(null, Services\Guarantee::b2c($attr1))
            ),
            'guarantee (fixed)' => array(
                $this->optionValueCollection(
                    null, null, Services\Guarantee::TIMEFIXED, $attr1 = '12:00'
                ),
                0,
                new Services(null, Services\Guarantee::timeFixed($attr1))
            ),
            'guarantee (fixed - missing attr1)' => array(
                $this->optionValueCollection(
                    null, null, Services\Guarantee::TIMEFIXED
                ),
                0,
                new Services()
            ),
            'cod' => array(
                $this->optionValueCollection(
                    null, null, null, null, false, false, false, $currency = 'EUR'
                ),
                $amount = $this->randomPositiveInt(1000000) / 100,
                new Services(
                    null, null, false, false, false, Services\Cod::create($amount, $currency)
                )
            ),
            'self col (priv)' => array(
                $this->optionValueCollection(
                    null, null, null, null, false, false, false, null, false, Services\SelfCol::PRIV
                ),
                0,
                new Services(
                    null, null, false, false, false, null, false, Services\SelfCol::priv()
                )
            ),
            'self col (comp)' => array(
                $this->optionValueCollection(
                    null, null, null, null, false, false, false, null, false, Services\SelfCol::COMP
                ),
                0,
                new Services(
                    null, null, false, false, false, null, false, Services\SelfCol::comp()
                )
            ),
            'self col (invalid)' => array(
                $this->optionValueCollection(
                    null, null, null, null, false, false, false, null, false, 'rubbish'
                ),
                0,
                new Services()
            )
        );
    }

    private function optionValueCollection(
        $declaredValueAmount = null,
        $declaredValueCurrency = null,
        $guranteeType = null,
        $guaranteeAttr1 = null,
        $cud = false,
        $dox = false,
        $rod = false,
        $codCurrency = null,
        $inPers = false,
        $selfCol = null,
        $privPers = false,
        $carryIn = false,
        $duty = false,
        $pallet = false
    ) {
        $optionValueCollection = new VendorOptionValueCollection();

        if ($declaredValueAmount) {
            $optionValueCollection->addValue($value = new VendorOptionValue(VendorOptions::DECLARED_VALUE_AMOUNT));
            $value->setValue($declaredValueAmount);
        }

        if ($declaredValueCurrency) {
            $optionValueCollection->addValue($value = new VendorOptionValue(VendorOptions::DECLARED_VALUE_CURRENCY));
            $value->setValue($declaredValueCurrency);
        }

        if ($guranteeType) {
            $optionValueCollection->addValue($value = new VendorOptionValue(VendorOptions::GUARANTEE_TYPE));
            $value->setValue($guranteeType);
        }

        if ($guaranteeAttr1) {
            $optionValueCollection->addValue($value = new VendorOptionValue(VendorOptions::GUARANTEE_ATTR1));
            $value->setValue($guaranteeAttr1);
        }

        $optionValueCollection->addValue($value = new VendorOptionValue(VendorOptions::CUD));
        $value->setValue($cud);

        $optionValueCollection->addValue($value = new VendorOptionValue(VendorOptions::DOX));
        $value->setValue($dox);

        $optionValueCollection->addValue($value = new VendorOptionValue(VendorOptions::ROD));
        $value->setValue($rod);

        if ($codCurrency) {
            $optionValueCollection->addValue($value = new VendorOptionValue(VendorOptions::COD_CURRENCY));
            $value->setValue($codCurrency);
        }

        $optionValueCollection->addValue($value = new VendorOptionValue(VendorOptions::IN_PERS));
        $value->setValue($inPers);

        if ($selfCol) {
            $optionValueCollection->addValue($value = new VendorOptionValue(VendorOptions::SELF_COL));
            $value->setValue($selfCol);
        }

        $optionValueCollection->addValue($value = new VendorOptionValue(VendorOptions::PRIV_PERS));
        $value->setValue($privPers);

        $optionValueCollection->addValue($value = new VendorOptionValue(VendorOptions::CARRY_IN));
        $value->setValue($carryIn);

        $optionValueCollection->addValue($value = new VendorOptionValue(VendorOptions::DUTY));
        $value->setValue($duty);

        $optionValueCollection->addValue($value = new VendorOptionValue(VendorOptions::PALLET));
        $value->setValue($pallet);

        return $optionValueCollection;
    }
}
