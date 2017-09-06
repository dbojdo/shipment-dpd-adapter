<?php
/**
 * File ParcelMapperTest.php
 * Created at: 2017-09-10 16:49
 *
 * @author Daniel Bojdo <daniel.bojdo@web-it.eu>
 */

namespace Webit\Shipment\DpdAdapter\Mapper\OpenUMLF;

use Webit\DPDClient\PackagesGeneration\OpenUMLF\Parcel;
use Webit\Shipment\DpdAdapter\AbstractTest;
use Webit\Shipment\Parcel\ParcelInterface;

class ParcelMapperTest extends AbstractTest
{
    /**
     * @var ParcelMapper
     */
    private $mapper;

    protected function setUp()
    {
        $this->mapper = new ParcelMapper();
    }

    /**
     * @param ParcelInterface $parcel
     * @param Parcel $expectedParcel
     * @dataProvider parcels
     * @test
     */
    public function shouldMapParcel(ParcelInterface $parcel, Parcel $expectedParcel)
    {
        $this->assertEquals($expectedParcel, $this->mapper->map($parcel));
    }

    public function parcels()
    {
        return array(
            array(
                $this->parcelInterface($weight = $this->randomPositiveInt() / 100, $reference = $this->randomString()),
                new Parcel($weight, $reference)
            )
        );
    }

    private function parcelInterface($weight, $reference)
    {
        $parcel = $this->prophesize('Webit\Shipment\Parcel\ParcelInterface');
        $parcel->getWeight()->willReturn($weight);
        $parcel->getReference()->willReturn($reference);

        return $parcel->reveal();
    }
}
