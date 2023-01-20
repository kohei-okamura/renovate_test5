<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Billing;

use Domain\Billing\LtcsBillingUser;
use Domain\Common\Carbon;
use Domain\Common\Sex;
use Domain\Common\StructuredName;
use Domain\LtcsInsCard\LtcsLevel;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Faker\Faker;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\Billing\LtcsBillingUser} のテスト.
 */
final class LtcsBillingUserTest extends Test
{
    use ExamplesConsumer;
    use MatchesSnapshots;
    use UnitSupport;

    private const SEED = 2141438069;

    /**
     * @test
     * @return void
     */
    public function describe_instance(): void
    {
        $this->should('return an instance', function (): void {
            $x = $this->createInstance();
            $this->assertMatchesModelSnapshot($x);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_from(): void
    {
        $this->should('return an instance', function (): void {
            // 初期化前に計算しておくことで乱数のぶれをなくす
            assert($this->examples->users !== null);

            Faker::seed(self::SEED);
            $user = $this->examples->generateUser([
                'id' => 1,
                'organizationId' => 2,
            ]);
            $ltcsInsCard = $this->examples->generateLtcsInsCard([
                'id' => 517,
                'userId' => $user->id,
            ]);
            $x = LtcsBillingUser::from($user, $ltcsInsCard);

            $this->assertMatchesModelSnapshot($x);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('be able to encode to json', function (): void {
            $x = $this->createInstance();
            $this->assertMatchesJsonSnapshot($x->toJson());
        });
    }

    /**
     * テスト対象のインスタンスを生成する.
     *
     * @param array $attrs
     * @return \Domain\Billing\LtcsBillingUser
     */
    private function createInstance(array $attrs = []): LtcsBillingUser
    {
        $x = new LtcsBillingUser(
            userId: 31959661,
            ltcsInsCardId: 66532668,
            insNumber: '1425832258',
            name: new StructuredName(
                familyName: '赤坂',
                givenName: '聡',
                phoneticFamilyName: 'アカサカ',
                phoneticGivenName: 'サトシ',
            ),
            sex: Sex::male(),
            birthday: Carbon::create(2008, 3, 7),
            ltcsLevel: LtcsLevel::careLevel1(),
            activatedOn: Carbon::create(1977, 2, 28),
            deactivatedOn: Carbon::create(1981, 11, 5),
        );
        return $x->copy($attrs);
    }
}
