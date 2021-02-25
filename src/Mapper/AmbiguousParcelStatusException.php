<?php

namespace Webit\Shipment\DpdAdapter\Mapper;

final class AmbiguousParcelStatusException extends \RuntimeException
{
    /**
     * @param int $businessCode
     */
    public function __construct($businessCode)
    {
        parent::__construct(
            sprintf(
                'Parcel\'s event business code of "%s" could not be distinctly mapped to the status',
                $businessCode
            )
        );
    }

    /**
     * @param int $businessCode
     * @return AmbiguousParcelStatusException
     */
    public static function create($businessCode)
    {
        return new self($businessCode);
    }
}