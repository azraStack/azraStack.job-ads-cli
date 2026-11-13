<?php

declare(strict_types=1);

namespace JobAds;

use InvalidArgumentException;

final readonly class SalaryStats
{
    public function __construct(
        public int $count,
        public int $lowest,
        public int $highest,
        public int $averageMidpoint,
    ) {}

    /**
     * @param  list<JobAd>  $ads
     */
    public static function fromAds(array $ads): self
    {
        if ($ads === []) {
            throw new InvalidArgumentException('Cannot compute salary stats for zero job ads.');
        }

        $midpoints = array_map(fn (JobAd $ad): int => $ad->midpoint(), $ads);

        return new self(
            count: count($ads),
            lowest: min(array_column($ads, 'salaryMin')),
            highest: max(array_column($ads, 'salaryMax')),
            averageMidpoint: intdiv(array_sum($midpoints), count($midpoints)),
        );
    }
}
