<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\User;

use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Location;
use Domain\Common\Prefecture;
use Domain\Common\Sex;
use Domain\Common\StructuredName;
use Domain\User\User;
use Domain\User\UserBillingDestination;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\User\User} のテスト
 */
final class UserTest extends Test
{
    use ExamplesConsumer;
    use UnitSupport;
    use CarbonMixin;
    use MatchesSnapshots;

    protected User $user;
    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self) {
            $self->values = [
                'id' => 1,
                'organizationId' => 1,
                'name' => new StructuredName(
                    familyName: '土屋',
                    givenName: '花子',
                    phoneticFamilyName: 'ツチヤ',
                    phoneticGivenName: 'ハナコ',
                ),
                'sex' => Sex::male(),
                'birthday' => Carbon::now(),
                'addr' => new Addr(
                    postcode: '164-0011',
                    prefecture: Prefecture::tokyo(),
                    city: '中野区',
                    street: '中央1-35-6',
                    apartment: 'レッチフィールド中野坂上ビル6F',
                ),
                'location' => Location::create(),
                'contacts' => [],
                'bankAccountId' => 1,
                'billingDestination' => UserBillingDestination::create(),
                'isEnabled' => true,
                'version' => 1,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            $self->user = User::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'id' => ['id'],
            'organizationId' => ['organizationId'],
            'name' => ['name'],
            'sex' => ['sex'],
            'birthday' => ['birthday'],
            'addr' => ['addr'],
            'location' => ['location'],
            'contacts' => ['contacts'],
            'bankAccountId' => ['bankAccountId'],
            'billingDestination' => ['billingDestination'],
            'isEnabled' => ['isEnabled'],
            'version' => ['version'],
            'createdAt' => ['createdAt'],
            'updatedAt' => ['updatedAt'],
        ];
        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->user->get($key), Arr::get($this->values, $key));
            },
            compact('examples')
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->user);
        });
    }
}
