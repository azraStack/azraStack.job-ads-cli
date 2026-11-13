<?php

declare(strict_types=1);

use JobAds\JobAd;
use JobAds\Seniority;

it('builds a job ad from a decoded JSON object', function () {
    $ad = JobAd::fromArray([
        'id' => 7,
        'company' => 'Demo Payments Sdn Bhd',
        'title' => 'Software Engineer, Payments',
        'seniority' => 'senior',
        'location' => 'Kuala Lumpur',
        'salary_min' => 8000,
        'salary_max' => 11000,
        'skills' => ['PHP', 'Symfony'],
    ]);

    expect($ad->id)->toBe(7)
        ->and($ad->seniority)->toBe(Seniority::Senior)
        ->and($ad->salaryMax)->toBe(11000)
        ->and($ad->skills)->toBe(['php', 'symfony']);
});

it('matches a skill whatever its letter case', function () {
    $ad = jobAd(['skills' => ['laravel', 'php']]);

    expect($ad->hasSkill('Laravel'))->toBeTrue()
        ->and($ad->hasSkill('react'))->toBeFalse();
});

it('uses the middle of the salary range as the midpoint', function () {
    $ad = jobAd(['salary_min' => 3800, 'salary_max' => 5200]);

    expect($ad->midpoint())->toBe(4500);
});

it('rejects a job ad with missing fields', function () {
    expect(fn () => JobAd::fromArray(['id' => 1, 'company' => 'Contoh Tech Sdn Bhd']))
        ->toThrow(InvalidArgumentException::class, 'missing field(s): title, seniority, location');
});

it('rejects a seniority it does not know', function () {
    expect(fn () => jobAd(['seniority' => 'intern']))
        ->toThrow(InvalidArgumentException::class, 'unknown seniority "intern"');
});
