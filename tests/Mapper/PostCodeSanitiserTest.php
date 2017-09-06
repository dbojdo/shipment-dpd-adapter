<?php

namespace Webit\Shipment\DpdAdapter\Mapper;

use Webit\Shipment\DpdAdapter\AbstractTest;

class PostCodeSanitiserTest extends AbstractTest
{
    /** @var PostCodeSanitiser */
    private $sanitiser;

    protected function setUp()
    {
        $this->sanitiser = new PostCodeSanitiser();
    }

    /**
     * @param $postCode
     * @param $sanitisePostCode
     * @test
     * @dataProvider postCodes
     */
    public function shouldSanitisePostCode($postCode, $sanitisePostCode)
    {
        $this->assertEquals($sanitisePostCode, $this->sanitiser->sanitise($postCode));
    }

    public function postCodes()
    {
        return array(
            array('PL30313', '30313'),
            array('01-006', '01006'),
            array('30 855', '30855'),
            array('PL 30-313', '30313')
        );
    }
}
