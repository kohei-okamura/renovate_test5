<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Carbon;
use Domain\Job\Job;
use Domain\Job\JobStatus;
use Faker\Generator;

/**
 * Job Examples.
 *
 * @property-read Job[] $jobs
 */
trait JobExample
{
    /**
     * ジョブの一覧を生成する.
     *
     * @return \Domain\Job\Job[]
     * @mixin \Tests\Unit\Examples\OrganizationExample
     * @mixin \Tests\Unit\Examples\StaffExample
     */
    protected function jobs(): array
    {
        return [
            $this->generateJob([
                'id' => 1,
                'organizationId' => $this->organizations[0]->id,
                'status' => JobStatus::waiting(),
                'data' => null,
                'token' => 'token',
            ]),
            $this->generateJob([
                'id' => 2,
                'organizationId' => $this->staffs[1]->organizationId,
                'staffId' => $this->staffs[1]->id,
                'status' => JobStatus::inProgress(),
                'data' => null,
            ]),
            $this->generateJob([
                'id' => 3,
                'organizationId' => $this->organizations[0]->id,
                'status' => JobStatus::success(),
            ]),
            $this->generateJob([
                'id' => 4,
                'organizationId' => $this->organizations[0]->id,
                'status' => JobStatus::failure(),
            ]),
        ];
    }

    /**
     * Generate an example of Job.
     *
     * @param array $overwrites
     * @return \Domain\Job\Job
     */
    private function generateJob(array $overwrites)
    {
        /** @var \Faker\Generator $faker */
        $faker = app(Generator::class);
        $dummyData = [];
        for ($i = 0; $i < $faker->numberBetween(1, 5); ++$i) {
            $dummyData += [$faker->word => $faker->word];
        }

        $values = [
            'staffId' => $this->staffs[0]->id,
            'data' => $dummyData,
            'token' => $faker->unique()->text(100),
            'createdAt' => Carbon::instance($faker->dateTime),
            'updatedAt' => Carbon::instance($faker->dateTime),
        ];
        return Job::create($overwrites + $values);
    }
}
