<?php

namespace Webit\Shipment\DpdAdapter\Mapper;

use Webit\DPDClient\DPDServices\DocumentGeneration\OutputDocFormatDSPEnumV1;
use Webit\DPDClient\DPDServices\DocumentGeneration\OutputDocPageFormatDSPEnumV1;

final class PrintModes
{
    /**
     * @return string[]
     */
    public static function modes()
    {
        return array(
            sprintf(
                '%s::%s',
                (string)OutputDocPageFormatDSPEnumV1::a4(),
                (string)OutputDocFormatDSPEnumV1::pdf()
            ),
            sprintf(
                '%s::%s',
                (string)OutputDocPageFormatDSPEnumV1::lblPrinter(),
                (string)OutputDocFormatDSPEnumV1::pdf()
            )
        );
    }
}