<?php

declare(strict_types=1);

namespace JobAds;

final readonly class JobAdFilter
{
    public function __construct(
        public ?string $skill = null,
        public ?int $minSalary = null,
        public ?Seniority $seniority = null,
    ) {}

    /**
     * @param  list<JobAd>  $ads
     * @return list<JobAd>
     */
    public function apply(array $ads): array
    {
        return array_values(array_filter($ads, $this->matches(...)));
    }

    public function matches(JobAd $ad): bool
    {
        if ($this->skill !== null && ! $ad->hasSkill($this->skill)) {
            return false;
        }

        if ($this->minSalary !== null && $ad->salaryMax < $this->minSalary) {
            return false;
        }

        if ($this->seniority !== null && $ad->seniority !== $this->seniority) {
            return false;
        }

        return true;
    }
}
