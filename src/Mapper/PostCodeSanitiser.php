<?php

namespace Webit\Shipment\DpdAdapter\Mapper;

class PostCodeSanitiser
{
    /**
     * @param string $postCode
     * @return string
     */
    public function sanitise($postCode)
    {
        return preg_replace('/\D+/', '', $postCode);
    }
}