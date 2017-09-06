<?php

namespace Webit\Shipment\DpdAdapter\Mapper\OpenUMLF;

use Webit\DPDClient\PackagesGeneration\OpenUMLF\Parcel;
use Webit\Shipment\Parcel\ParcelInterface;

class ParcelMapper
{
    /**
     * @param ParcelInterface $parcel
     * @return Parcel
     */
    public function map(ParcelInterface $parcel)
    {
        return new Parcel(
            $parcel->getWeight(),
            $parcel->getReference()
        );
    }
}
