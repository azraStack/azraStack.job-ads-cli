<?php

use JobAds\JobAd;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Build a JobAd from sensible defaults, overriding only what a test cares about.
 *
 * @param  array<string, mixed>  $overrides
 */
function jobAd(array $overrides = []): JobAd
{
    return JobAd::fromArray(array_merge([
        'id' => 1,
        'company' => 'Contoh Tech Sdn Bhd',
        'title' => 'Software Engineer',
        'seniority' => 'junior',
        'location' => 'Kuala Lumpur',
        'salary_min' => 3000,
        'salary_max' => 5000,
        'skills' => ['php'],
    ], $overrides));
}
