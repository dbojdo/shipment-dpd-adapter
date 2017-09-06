<?php
/**
 * File Sender.php
 * Created at: 2017-09-10 08:33
 *
 * @author Daniel Bojdo <daniel.bojdo@web-it.eu>
 */

namespace Webit\Shipment\DpdAdapter\Mapper\OpenUMLF;

use Webit\DPDClient\PackagesGeneration\OpenUMLF\Sender;
use Webit\Shipment\Address\SenderAddressInterface;

class SenderMapper
{
    /**
     * @var int
     */
    private $fid;

    /**
     * SenderMapper constructor.
     * @param int $fid
     */
    public function __construct($fid)
    {
        $this->fid = $fid;
    }

    /**
     * @param SenderAddressInterface $address
     * @return Sender
     */
    public function map(SenderAddressInterface $address = null)
    {
        if (! $address) {
            return Sender::fromFid($this->fid);
        }

        return new Sender(
            $address->getName(),
            $address->getAddress(),
            $address->getPost(),
            $address->getPostCode(),
            $address->getCountry()->getIsoCode(),
            $this->fid
        );
    }
}