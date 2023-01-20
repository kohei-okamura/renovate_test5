<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Staff;

use Domain\Common\Carbon;
use Domain\Staff\StaffRememberToken;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Examples\OrganizationExample;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * StaffRememberToken のテスト
 */
class StaffRememberTokenTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use OrganizationExample;
    use MatchesSnapshots;
    use UnitSupport;

    protected StaffRememberToken $staffRememberToken;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (StaffRememberTokenTest $self): void {
            $self->values = [
                'id' => 1,
                'staffId' => 1,
                'token' => 'MOE3wl7BGnsb0322mZituY6C423p4Q3PXAurqDczOZqN1bjCbaT09Dgw6CbU',
                'expiredAt' => Carbon::now()->addMonth(),
                'createdAt' => Carbon::now(),
            ];
            $self->staffRememberToken = StaffRememberToken::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have specified attribute', function (string $key): void {
            $this->assertSame($this->staffRememberToken->get($key), Arr::get($this->values, $key));
        }, [
            'examples' => [
                'id' => ['id'],
                'staffId' => ['staffId'],
                'token' => ['token'],
                'expiredAt' => ['expiredAt'],
                'createdAt' => ['createdAt'],
            ],
        ]);
    }

    /**
     * @test
     * @return void
     */
    public function describe_isExpired(): void
    {
        $this->should('return value same as isPast', function (): void {
            $this->assertSame($this->staffRememberToken->isExpired(), $this->staffRememberToken->expiredAt->isPast());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_isNotExpired(): void
    {
        $this->should('return value same as not isPast', function (): void {
            $this->assertSame($this->staffRememberToken->isNotExpired(), !$this->staffRememberToken->expiredAt->isPast());
        });
    }
}
