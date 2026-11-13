<?php

declare(strict_types=1);

namespace JobAds;

use InvalidArgumentException;
use JsonException;
use TypeError;

final readonly class JobAdRepository
{
    public function __construct(private string $path) {}

    /**
     * @return list<JobAd>
     */
    public function all(): array
    {
        if (! is_file($this->path)) {
            throw InvalidJobAdsFile::notFound($this->path);
        }

        try {
            $rows = json_decode((string) file_get_contents($this->path), associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw InvalidJobAdsFile::invalidJson($this->path, $e);
        }

        if (! is_array($rows) || ! array_is_list($rows)) {
            throw InvalidJobAdsFile::notAList($this->path);
        }

        $ads = [];

        foreach ($rows as $index => $row) {
            try {
                $ads[] = JobAd::fromArray($row);
            } catch (InvalidArgumentException|TypeError $e) {
                throw InvalidJobAdsFile::invalidAd($this->path, $index + 1, $e);
            }
        }

        return $ads;
    }
}
