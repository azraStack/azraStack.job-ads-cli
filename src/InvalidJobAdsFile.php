<?php

declare(strict_types=1);

namespace JobAds;

use JsonException;
use RuntimeException;
use Throwable;

final class InvalidJobAdsFile extends RuntimeException
{
    public static function notFound(string $path): self
    {
        return new self("Job ads file not found: {$path}");
    }

    public static function invalidJson(string $path, JsonException $previous): self
    {
        return new self("Job ads file is not valid JSON: {$path} ({$previous->getMessage()})", previous: $previous);
    }

    public static function notAList(string $path): self
    {
        return new self("Job ads file must contain a JSON array of job ads: {$path}");
    }

    public static function invalidAd(string $path, int $position, Throwable $previous): self
    {
        return new self("Job ad #{$position} in {$path} is invalid: {$previous->getMessage()}", previous: $previous);
    }
}
