<?php
/**
 * File ReceiverMapper.php
 * Created at: 2017-09-10 08:37
 *
 * @author Daniel Bojdo <daniel.bojdo@web-it.eu>
 */

namespace Webit\Shipment\DpdAdapter\Mapper\OpenUMLF;

use Webit\DPDClient\PackagesGeneration\OpenUMLF\Receiver;
use Webit\Shipment\Address\DeliveryAddressInterface;

class ReceiverMapper
{
    /**
     * @param DeliveryAddressInterface $address
     * @return Receiver
     */
    public function map(DeliveryAddressInterface $address)
    {
        $receiver = new Receiver(
            $address->getName(),
            $address->getAddress(),
            $address->getPost(),
            $address->getPostCode(),
            $address->getCountry()->getIsoCode(),
            null,
            $address->getContactPerson(),
            $address->getContactPhoneNo(),
            $address->getContactEmail()
        );

        return $receiver;
    }
}
