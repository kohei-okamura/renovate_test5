<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Staff;

use Domain\Common\Carbon;
use Domain\Staff\StaffEmailVerification;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Examples\OrganizationExample;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * StaffEmailVerification のテスト
 */
class StaffEmailVerificationTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use OrganizationExample;
    use MatchesSnapshots;
    use UnitSupport;

    protected StaffEmailVerification $staffEmailVerification;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (StaffEmailVerificationTest $self): void {
            $self->values = [
                'id' => 1,
                'name' => '名前',
                'email' => 'sample@example.com',
                'token' => 'MOE3wl7BGnsb0322mZituY6C423p4Q3PXAurqDczOZqN1bjCbaT09Dgw6CbU',
                'expiredAt' => Carbon::now()->addMonth(),
                'createdAt' => Carbon::now(),
            ];
            $self->staffEmailVerification = StaffEmailVerification::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have id attribute', function (string $key): void {
            $this->assertSame($this->staffEmailVerification->get($key), Arr::get($this->values, $key));
        }, [
            'examples' => [
                'id' => ['id'],
                'name' => ['name'],
                'email' => ['email'],
                'token' => ['token'],
                'expiredAt' => ['expiredAt'],
                'createdAt' => ['createdAt'],
            ],
        ]);
    }
}
