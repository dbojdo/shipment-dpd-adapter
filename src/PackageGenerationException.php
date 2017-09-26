<?php

namespace Webit\Shipment\DpdAdapter;

use Webit\DPDClient\DPDServices\PackagesGeneration\PackagesGenerationResponseV3;

class PackageGenerationException extends DpdAdapterException
{
    /** @var PackagesGenerationResponseV3 */
    private $packageGenerationResponse;

    /**
     * @param PackagesGenerationResponseV3 $generationResponseV3
     * @return PackageGenerationException
     */
    public static function fromPackageGenerationResponse(PackagesGenerationResponseV3 $generationResponseV3)
    {
        $e = new self(
            sprintf('GeneratePackagesNumbersV3 return status "%s".', $generationResponseV3->status())
        );

        $e->packageGenerationResponse = $generationResponseV3;

        return $e;
    }

    /**
     * @return PackagesGenerationResponseV3
     */
    public function packageGenerationResponse()
    {
        return $this->packageGenerationResponse;
    }
}