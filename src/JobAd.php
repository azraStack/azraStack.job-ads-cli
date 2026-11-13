<?php

declare(strict_types=1);

namespace JobAds;

use InvalidArgumentException;

final readonly class JobAd
{
    private const array REQUIRED_FIELDS = [
        'id', 'company', 'title', 'seniority', 'location', 'salary_min', 'salary_max', 'skills',
    ];

    /**
     * @param  list<string>  $skills
     */
    public function __construct(
        public int $id,
        public string $company,
        public string $title,
        public Seniority $seniority,
        public string $location,
        public int $salaryMin,
        public int $salaryMax,
        public array $skills,
    ) {}

    /**
     * Build a JobAd from one decoded JSON object.
     *
     * @param  array<mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $missing = array_diff(self::REQUIRED_FIELDS, array_keys($data));

        if ($missing !== []) {
            throw new InvalidArgumentException('missing field(s): '.implode(', ', $missing));
        }

        $seniority = Seniority::tryFrom($data['seniority'])
            ?? throw new InvalidArgumentException("unknown seniority \"{$data['seniority']}\"");

        return new self(
            id: $data['id'],
            company: $data['company'],
            title: $data['title'],
            seniority: $seniority,
            location: $data['location'],
            salaryMin: $data['salary_min'],
            salaryMax: $data['salary_max'],
            skills: array_map(strtolower(...), $data['skills']),
        );
    }

    public function hasSkill(string $skill): bool
    {
        return in_array(strtolower($skill), $this->skills, true);
    }

    public function midpoint(): int
    {
        return intdiv($this->salaryMin + $this->salaryMax, 2);
    }
}
