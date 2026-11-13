<?php

declare(strict_types=1);

namespace JobAds;

enum Seniority: string
{
    case Junior = 'junior';
    case Mid = 'mid';
    case Senior = 'senior';

    public function label(): string
    {
        return match ($this) {
            Seniority::Junior => 'Junior',
            Seniority::Mid => 'Mid-level',
            Seniority::Senior => 'Senior',
        };
    }
}
