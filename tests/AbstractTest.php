<?php

namespace Webit\Shipment\DpdAdapter;

use Faker\Factory;
use Faker\Generator;
use Webit\Addressing\Model\Country;
use Webit\Shipment\Address\SenderAddressInterface;

abstract class AbstractTest extends \PHPUnit_Framework_TestCase
{
    /** @var Generator */
    private $faker;

    /**
     * @param int $min
     * @param int $max
     * @return int
     */
    protected function randomInt($min = null, $max = PHP_INT_MAX)
    {
        $min = (int)($min === null ? -PHP_INT_MAX : $min);
        $max = (int)($max === null ? PHP_INT_MAX : $max);

        return mt_rand($min, $max);
    }

    /**
     * @param int $max
     * @return int
     */
    protected function randomPositiveInt($max = PHP_INT_MAX)
    {
        return $this->randomInt(1, $max);
    }

    /**
     * @param int $length
     * @return bool|string
     */
    protected function randomString($length = 32)
    {
        $string = '';
        do {
            $string .= md5(microtime().$this->randomInt());
        } while (strlen($string) < $length);

        return substr($string, 0, $length);
    }

    /**
     * @return bool
     */
    protected function randomBoolean()
    {
        return (bool)$this->randomInt(0, 1);
    }

    /**
     * @return Generator
     */
    protected function faker()
    {
        if (! $this->faker) {
            $this->faker = Factory::create('pl_PL');
        }

        return $this->faker;
    }

    /**
     * @param $name
     * @param $address
     * @param $post
     * @param $postCode
     * @param $countryCode
     * @return SenderAddressInterface
     */
    protected function sender($name, $address, $post, $postCode, $countryCode)
    {
        $sender = $this->prophesize('Webit\Shipment\Address\SenderAddressInterface');

        $sender->getName()->willReturn($name);
        $sender->getAddress()->willReturn($address);
        $sender->getPost()->willReturn($post);
        $sender->getPostCode()->willReturn($postCode);
        $sender->getCountry()->willReturn(new Country($this->randomString(), $countryCode));

        return $sender->reveal();
    }
}