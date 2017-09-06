<?php
/**
 * File PrintModes.php
 * Created at: 2017-09-10 08:30
 *
 * @author Daniel Bojdo <daniel.bojdo@web-it.eu>
 */

namespace Webit\Shipment\DpdAdapter\Mapper;

use Webit\DPDClient\DocumentGeneration\OutputDocFormatDSPEnumV1;
use Webit\DPDClient\DocumentGeneration\OutputDocPageFormatDSPEnumV1;

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