<?php
/**
 * File ParcelMapper.php
 * Created at: 2017-09-10 08:38
 *
 * @author Daniel Bojdo <daniel.bojdo@web-it.eu>
 */

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
