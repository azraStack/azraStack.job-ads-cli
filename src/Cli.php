<?php

declare(strict_types=1);

namespace JobAds;

use InvalidArgumentException;

final readonly class Cli
{
    public const int SUCCESS = 0;

    public const int FAILURE = 1;

    public const int USAGE_ERROR = 2;

    public function __construct(private JobAdRepository $repository) {}

    /**
     * @param  list<string>  $argv  The raw arguments, e.g. ['bin/jobs', 'filter', '--skill=laravel']
     */
    public function run(array $argv): int
    {
        $command = $argv[1] ?? null;

        try {
            $options = $this->parseOptions(array_slice($argv, 2));

            return match ($command) {
                'list' => $this->list(),
                'filter' => $this->filter($options),
                'stats' => $this->stats(),
                'help', '--help' => $this->help(),
                null => throw new InvalidArgumentException('No command given.'),
                default => throw new InvalidArgumentException("Unknown command \"{$command}\"."),
            };
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage().PHP_EOL.PHP_EOL.$this->usage());

            return self::USAGE_ERROR;
        } catch (InvalidJobAdsFile $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * Turn ['--skill=laravel', '--min-salary=5000'] into ['skill' => 'laravel', 'min-salary' => '5000'].
     *
     * @param  list<string>  $arguments
     * @return array<string, string>
     */
    private function parseOptions(array $arguments): array
    {
        $options = [];

        foreach ($arguments as $argument) {
            if (! str_starts_with($argument, '--') || ! str_contains($argument, '=')) {
                throw new InvalidArgumentException("Options look like --name=value, got \"{$argument}\".");
            }

            [$name, $value] = explode('=', substr($argument, 2), 2);
            $options[$name] = $value;
        }

        return $options;
    }

    private function list(): int
    {
        $ads = $this->repository->all();
        $this->printTable($ads);
        echo PHP_EOL.count($ads).' job ads.'.PHP_EOL;

        return self::SUCCESS;
    }

    /**
     * @param  array<string, string>  $options
     */
    private function filter(array $options): int
    {
        $unknown = array_diff(array_keys($options), ['skill', 'min-salary']);

        if ($unknown !== []) {
            throw new InvalidArgumentException('Unknown option(s): --'.implode(', --', $unknown).'.');
        }

        $minSalary = $options['min-salary'] ?? null;

        if ($minSalary !== null && ! ctype_digit($minSalary)) {
            throw new InvalidArgumentException('--min-salary must be a whole number of ringgit, e.g. --min-salary=5000.');
        }

        $filter = new JobAdFilter(
            skill: $options['skill'] ?? null,
            minSalary: $minSalary === null ? null : (int) $minSalary,
        );

        $all = $this->repository->all();
        $matches = $filter->apply($all);

        $this->printTable($matches);
        echo PHP_EOL.count($matches).' of '.count($all).' job ads match.'.PHP_EOL;

        return self::SUCCESS;
    }

    private function stats(): int
    {
        $ads = $this->repository->all();

        if ($ads === []) {
            echo 'No job ads yet.'.PHP_EOL;

            return self::SUCCESS;
        }

        $all = SalaryStats::fromAds($ads);
        printf("Job ads:          %d\n", $all->count);
        printf("Salary range:     RM %s - %s per month\n", number_format($all->lowest), number_format($all->highest));
        printf("Average midpoint: RM %s per month\n\n", number_format($all->averageMidpoint));
        echo 'By seniority:'.PHP_EOL;

        foreach (Seniority::cases() as $level) {
            $group = (new JobAdFilter(seniority: $level))->apply($ads);

            if ($group === []) {
                continue;
            }

            $stats = SalaryStats::fromAds($group);
            printf(
                "  %-10s %2d ads, average midpoint RM %s\n",
                $level->label(),
                $stats->count,
                number_format($stats->averageMidpoint),
            );
        }

        return self::SUCCESS;
    }

    private function help(): int
    {
        echo $this->usage().PHP_EOL;

        return self::SUCCESS;
    }

    /**
     * @param  list<JobAd>  $ads
     */
    private function printTable(array $ads): void
    {
        printf("%-3s %-28s %-22s %-10s %s\n", 'ID', 'TITLE', 'COMPANY', 'LEVEL', 'RM/MONTH');

        foreach ($ads as $ad) {
            printf(
                "%-3d %-28s %-22s %-10s %s - %s\n",
                $ad->id,
                $ad->title,
                $ad->company,
                $ad->seniority->label(),
                number_format($ad->salaryMin),
                number_format($ad->salaryMax),
            );
        }
    }

    private function usage(): string
    {
        return <<<'TEXT'
            Usage: php bin/jobs <command> [options]

            Commands:
              list                     List every job ad
              filter [options]         List the job ads that match every option given
                --skill=<skill>          e.g. --skill=laravel
                --min-salary=<ringgit>   the ad's range reaches at least this much per month
              stats                    Salary statistics for all job ads
              help                     Show this message
            TEXT;
    }

    private function error(string $message): void
    {
        fwrite(STDERR, 'Error: '.$message.PHP_EOL);
    }
}
