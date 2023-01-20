<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\User;

use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\DefrayerCategory;
use Domain\User\UserLtcsSubsidy;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\User\UserLtcsSubsidy} Test.
 */
class UserLtcsSubsidyTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use UnitSupport;

    protected UserLtcsSubsidy $domain;
    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UserLtcsSubsidyTest $self): void {
            $self->values = [
                'userId' => $self->examples->userLtcsSubsidies[0]->userId,
                'period' => CarbonRange::create(),
                'defrayerCategory' => DefrayerCategory::atomicBombVictim(),
                'defrayerNumber' => '12345',
                'recipientNumber' => '67890',
                'benefitRate' => 10,
                'copay' => 20,
                'isEnabled' => true,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            $self->domain = UserLtcsSubsidy::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have specified attribute', function (string $key, $expected = null): void {
            $this->assertEquals($this->domain->copy([
                'version' => 1,
            ])->get($key), $expected ?? Arr::get($this->values, $key));
        }, [
            'examples' => [
                'userId' => ['userId'],
                'period' => ['period'],
                'defrayerCategory' => ['defrayerCategory'],
                'defrayerNumber' => ['defrayerNumber'],
                'recipientNumber' => ['recipientNumber'],
                'benefitRate' => ['benefitRate'],
                'copay' => ['copay'],
                'isEnabled' => ['isEnabled'],
                'version' => ['version', 1],
                'createdAt' => ['createdAt'],
                'updatedAt' => ['updatedAt'],
            ],
        ]);
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->domain);
        });
    }
}
