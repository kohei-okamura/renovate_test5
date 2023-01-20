<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\User;

use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Rounding;
use Domain\User\UserDwsSubsidy;
use Domain\User\UserDwsSubsidyFactor;
use Domain\User\UserDwsSubsidyType;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\User\UserDwsSubsidy} Test.
 */
class UserDwsSubsidyTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use UnitSupport;

    protected UserDwsSubsidy $domain;
    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UserDwsSubsidyTest $self): void {
            $self->values = [
                'id' => $self->examples->userDwsSubsidies[0]->id,
                'userId' => $self->examples->userDwsSubsidies[0]->userId,
                'period' => CarbonRange::create([
                    'start' => Carbon::now(),
                    'end' => Carbon::now(),
                ]),
                'cityName' => '中野区',
                'cityCode' => '131078',
                'subsidyType' => UserDwsSubsidyType::benefitAmount(),
                'factor' => UserDwsSubsidyFactor::copay(),
                'benefitRate' => 0,
                'copayRate' => 0,
                'rounding' => Rounding::floor(),
                'benefitAmount' => 10000,
                'copayAmount' => 3000,
                'note' => 'NOTE',
            ];
            $self->domain = UserDwsSubsidy::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have specified attribute', function (string $key, $expected = null): void {
            $this->assertEquals($this->domain->get($key), $expected ?? Arr::get($this->values, $key));
        }, [
            'examples' => [
                'id' => ['id'],
                'userId' => ['userId'],
                'period' => ['period'],
                'cityName' => ['cityName'],
                'cityCode' => ['cityCode'],
                'subsidyType' => ['subsidyType'],
                'factor' => ['factor'],
                'benefitRate' => ['benefitRate'],
                'copayRate' => ['copayRate'],
                'rounding' => ['rounding'],
                'benefitAmount' => ['benefitAmount'],
                'copayAmount' => ['copayAmount'],
                'note' => ['note'],
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

    /**
     * @test
     * @return void
     */
    public function describe_compute(): void
    {
        $this->should('return subsidy amount when benefitRate and factor is none and rounding is none', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::benefitRate(),
                'factor' => UserDwsSubsidyFactor::none(),
                'rounding' => Rounding::none(),
                'benefitRate' => 40, // 給付率40%
                'copayRate' => 20, // 負担率20%
            ] + $this->values);
            $this->assertSame(603, $entity->compute(1506, 10000));
        });
        $this->should('return subsidy amount when benefitRate and factor is none and rounding is floor', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::benefitRate(),
                'factor' => UserDwsSubsidyFactor::none(),
                'rounding' => Rounding::floor(),
                'benefitRate' => 40, // 給付率40%
                'copayRate' => 20, // 負担率20%
            ] + $this->values);
            $this->assertSame(602, $entity->compute(1506, 10000));
        });
        $this->should('return subsidy amount when benefitRate and factor is none and rounding is ceil', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::benefitRate(),
                'factor' => UserDwsSubsidyFactor::none(),
                'rounding' => Rounding::ceil(),
                'benefitRate' => 40, // 給付率40%
                'copayRate' => 20, // 負担率20%
            ] + $this->values);
            $this->assertSame(603, $entity->compute(1506, 10000));
        });
        $this->should('return subsidy amount when benefitRate and factor is none and rounding is round up', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::benefitRate(),
                'factor' => UserDwsSubsidyFactor::none(),
                'rounding' => Rounding::round(),
                'benefitRate' => 40, // 給付率40%
                'copayRate' => 20, // 負担率20%
            ] + $this->values);
            $this->assertSame(603, $entity->compute(1507, 10000));
        });
        $this->should('return subsidy amount when benefitRate and factor is none and rounding is round down', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::benefitRate(),
                'factor' => UserDwsSubsidyFactor::none(),
                'rounding' => Rounding::round(),
                'benefitRate' => 40, // 給付率40%
                'copayRate' => 20, // 負担率20%
            ] + $this->values);
            $this->assertSame(602, $entity->compute(1506, 10000));
        });
        $this->should('return subsidy amount when benefitRate and factor is copay and rounding is none', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::benefitRate(),
                'factor' => UserDwsSubsidyFactor::copay(),
                'rounding' => Rounding::none(),
                'benefitRate' => 40, // 給付率40%
                'copayRate' => 20, // 負担率20%
            ] + $this->values);
            $this->assertSame(603, $entity->compute(1506, 10000));
        });
        $this->should('return subsidy amount when benefitRate and factor is copay and rounding is floor', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::benefitRate(),
                'factor' => UserDwsSubsidyFactor::copay(),
                'rounding' => Rounding::floor(),
                'benefitRate' => 40, // 給付率40%
                'copayRate' => 20, // 負担率20%
            ] + $this->values);
            $this->assertSame(602, $entity->compute(1506, 10000));
        });
        $this->should('return subsidy amount when benefitRate and factor is copay and rounding is ceil', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::benefitRate(),
                'factor' => UserDwsSubsidyFactor::copay(),
                'rounding' => Rounding::ceil(),
                'benefitRate' => 40, // 給付率40%
                'copayRate' => 20, // 負担率20%
            ] + $this->values);
            $this->assertSame(603, $entity->compute(1506, 10000));
        });
        $this->should('return subsidy amount when benefitRate and factor is copay and rounding is round up', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::benefitRate(),
                'factor' => UserDwsSubsidyFactor::copay(),
                'rounding' => Rounding::round(),
                'benefitRate' => 40, // 給付率40%
                'copayRate' => 20, // 負担率20%
            ] + $this->values);
            $this->assertSame(603, $entity->compute(1507, 10000));
        });
        $this->should('return subsidy amount when benefitRate and factor is copay and rounding is round down', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::benefitRate(),
                'factor' => UserDwsSubsidyFactor::copay(),
                'rounding' => Rounding::round(),
                'benefitRate' => 40, // 給付率40%
                'copayRate' => 20, // 負担率20%
            ] + $this->values);
            $this->assertSame(602, $entity->compute(1506, 10000));
        });
        $this->should('return subsidy amount when benefitRate and factor is fee and rounding is none', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::benefitRate(),
                'factor' => UserDwsSubsidyFactor::fee(),
                'rounding' => Rounding::none(),
                'benefitRate' => 40, // 給付率40%
                'copayRate' => 20, // 負担率20%
            ] + $this->values);
            $this->assertSame(603, $entity->compute(10000, 1506));
        });
        $this->should('return subsidy amount when benefitRate and factor is fee and rounding is floor', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::benefitRate(),
                'factor' => UserDwsSubsidyFactor::fee(),
                'rounding' => Rounding::floor(),
                'benefitRate' => 40, // 給付率40%
                'copayRate' => 20, // 負担率20%
            ] + $this->values);
            $this->assertSame(602, $entity->compute(10000, 1506));
        });
        $this->should('return subsidy amount when benefitRate and factor is fee and rounding is ceil', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::benefitRate(),
                'factor' => UserDwsSubsidyFactor::fee(),
                'rounding' => Rounding::ceil(),
                'benefitRate' => 40, // 給付率40%
                'copayRate' => 20, // 負担率20%
            ] + $this->values);
            $this->assertSame(603, $entity->compute(10000, 1506));
        });
        $this->should('return subsidy amount when benefitRate and factor is fee and rounding is round up', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::benefitRate(),
                'factor' => UserDwsSubsidyFactor::fee(),
                'rounding' => Rounding::round(),
                'benefitRate' => 40, // 給付率40%
                'copayRate' => 20, // 負担率20%
            ] + $this->values);
            $this->assertSame(603, $entity->compute(10000, 1507));
        });
        $this->should('return subsidy amount when benefitRate and factor is fee and rounding is round down', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::benefitRate(),
                'factor' => UserDwsSubsidyFactor::fee(),
                'rounding' => Rounding::round(),
                'benefitRate' => 40, // 給付率40%
                'copayRate' => 20, // 負担率20%
            ] + $this->values);
            $this->assertSame(602, $entity->compute(10000, 1506));
        });
        $this->should('return subsidy amount is subtotalCopay when subtotalCopay over than subsidy amount and factor is fee', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::benefitRate(),
                'factor' => UserDwsSubsidyFactor::fee(),
                'rounding' => Rounding::round(),
                'benefitRate' => 40, // 給付率40%
                'copayRate' => 20, // 負担率20%
            ] + $this->values);
            $this->assertSame(2000, $entity->compute(2000, 10000));
        });
        $this->should('return subsidy amount when copayRate and factor is none and rounding is none', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::copayRate(),
                'factor' => UserDwsSubsidyFactor::none(),
                'rounding' => Rounding::none(),
                'benefitRate' => 20, // 給付率20%
                'copayRate' => 40, // 負担率40%
            ] + $this->values);
            $this->assertSame(903, $entity->compute(1506, 10000));
        });
        $this->should('return subsidy amount when copayRate and factor is none and rounding is floor', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::copayRate(),
                'factor' => UserDwsSubsidyFactor::none(),
                'rounding' => Rounding::floor(),
                'benefitRate' => 20, // 給付率20%
                'copayRate' => 40, // 負担率40%
            ] + $this->values);
            $this->assertSame(904, $entity->compute(1506, 10000));
        });
        $this->should('return subsidy amount when copayRate and factor is none and rounding is ceil', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::copayRate(),
                'factor' => UserDwsSubsidyFactor::none(),
                'rounding' => Rounding::ceil(),
                'benefitRate' => 20, // 給付率20%
                'copayRate' => 40, // 負担率40%
            ] + $this->values);
            $this->assertSame(903, $entity->compute(1506, 10000));
        });
        $this->should('return subsidy amount when copayRate and factor is none and rounding is round up', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::copayRate(),
                'factor' => UserDwsSubsidyFactor::none(),
                'rounding' => Rounding::round(),
                'benefitRate' => 20, // 給付率20%
                'copayRate' => 40, // 負担率40%
            ] + $this->values);
            $this->assertSame(904, $entity->compute(1507, 10000));
        });
        $this->should('return subsidy amount when copayRate and factor is none and rounding is round down', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::copayRate(),
                'factor' => UserDwsSubsidyFactor::none(),
                'rounding' => Rounding::round(),
                'benefitRate' => 20, // 給付率20%
                'copayRate' => 40, // 負担率40%
            ] + $this->values);
            $this->assertSame(904, $entity->compute(1506, 10000));
        });
        $this->should('return subsidy amount when copayRate and factor is copay and rounding is none', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::copayRate(),
                'factor' => UserDwsSubsidyFactor::copay(),
                'rounding' => Rounding::none(),
                'benefitRate' => 20, // 給付率20%
                'copayRate' => 40, // 負担率40%
            ] + $this->values);
            $this->assertSame(903, $entity->compute(1506, 10000));
        });
        $this->should('return subsidy amount when copayRate and factor is copay and rounding is floor', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::copayRate(),
                'factor' => UserDwsSubsidyFactor::copay(),
                'rounding' => Rounding::floor(),
                'benefitRate' => 20, // 給付率20%
                'copayRate' => 40, // 負担率40%
            ] + $this->values);
            $this->assertSame(904, $entity->compute(1506, 10000));
        });
        $this->should('return subsidy amount when copayRate and factor is copay and rounding is ceil', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::copayRate(),
                'factor' => UserDwsSubsidyFactor::copay(),
                'rounding' => Rounding::ceil(),
                'benefitRate' => 20, // 給付率20%
                'copayRate' => 40, // 負担率40%
            ] + $this->values);
            $this->assertSame(903, $entity->compute(1506, 10000));
        });
        $this->should('return subsidy amount when copayRate and factor is copay and rounding is round up', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::copayRate(),
                'factor' => UserDwsSubsidyFactor::copay(),
                'rounding' => Rounding::round(),
                'benefitRate' => 20, // 給付率20%
                'copayRate' => 40, // 負担率40%
            ] + $this->values);
            $this->assertSame(904, $entity->compute(1507, 10000));
        });
        $this->should('return subsidy amount when copayRate and factor is copay and rounding is round down', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::copayRate(),
                'factor' => UserDwsSubsidyFactor::copay(),
                'rounding' => Rounding::round(),
                'benefitRate' => 20, // 給付率20%
                'copayRate' => 40, // 負担率40%
            ] + $this->values);
            $this->assertSame(904, $entity->compute(1506, 10000));
        });
        $this->should('return subsidy amount when copayRate and factor is fee and rounding is none', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::copayRate(),
                'factor' => UserDwsSubsidyFactor::fee(),
                'rounding' => Rounding::none(),
                'benefitRate' => 20, // 給付率20%
                'copayRate' => 40, // 負担率40%
            ] + $this->values);
            $this->assertSame(9397, $entity->compute(10000, 1506));
        });
        $this->should('return subsidy amount when copayRate and factor is fee and rounding is floor', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::copayRate(),
                'factor' => UserDwsSubsidyFactor::fee(),
                'rounding' => Rounding::floor(),
                'benefitRate' => 20, // 給付率20%
                'copayRate' => 40, // 負担率40%
            ] + $this->values);
            $this->assertSame(9398, $entity->compute(10000, 1506));
        });
        $this->should('return subsidy amount when copayRate and factor is fee and rounding is ceil', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::copayRate(),
                'factor' => UserDwsSubsidyFactor::fee(),
                'rounding' => Rounding::ceil(),
                'benefitRate' => 20, // 給付率20%
                'copayRate' => 40, // 負担率40%
            ] + $this->values);
            $this->assertSame(9397, $entity->compute(10000, 1506));
        });
        $this->should('return subsidy amount when copayRate and factor is fee and rounding is round up', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::copayRate(),
                'factor' => UserDwsSubsidyFactor::fee(),
                'rounding' => Rounding::round(),
                'benefitRate' => 20, // 給付率20%
                'copayRate' => 40, // 負担率40%
            ] + $this->values);
            $this->assertSame(9397, $entity->compute(10000, 1507));
        });
        $this->should('return subsidy amount when copayRate and factor is fee and rounding is round down', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::copayRate(),
                'factor' => UserDwsSubsidyFactor::fee(),
                'rounding' => Rounding::round(),
                'benefitRate' => 20, // 給付率20%
                'copayRate' => 40, // 負担率40%
            ] + $this->values);
            $this->assertSame(9398, $entity->compute(10000, 1506));
        });
        $this->should('return subsidy amount is subtotalCopay when subtotalCopay over than subsidy amount', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::copayRate(),
                'factor' => UserDwsSubsidyFactor::fee(),
                'rounding' => Rounding::round(),
                'benefitRate' => 20, // 給付率20%
                'copayRate' => 40, // 負担率40%
            ] + $this->values);
            $this->assertSame(0, $entity->compute(2000, 10000));
        });
        $this->should('return subsidy amount when copayAmount', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::copayAmount(),
                'copayAmount' => 3000,
            ] + $this->values);
            $this->assertSame(2000, $entity->compute(5000, 10000));
        });
        $this->should('return subsidy amount is 0 when copayAmount and not over copay amount', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::copayAmount(),
                'copayAmount' => 3000,
            ] + $this->values);
            $this->assertSame(0, $entity->compute(1000, 10000));
        });
        $this->should('return subsidy amount when benefitAmount and subtotalCopay smaller than benefitAmount', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::benefitAmount(),
                'benefitAmount' => 3000,
            ] + $this->values);
            $this->assertSame(1000, $entity->compute(1000, 10000));
        });
        $this->should('return subsidy amount when benefitAmount and benefitAmount smaller than subtotalCopay', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::benefitAmount(),
                'benefitAmount' => 3000,
            ] + $this->values);
            $this->assertSame(3000, $entity->compute(10000, 10000));
        });
        $this->should('return subsidy amount is subtotalCopay when subtotalCopay over than subsidy amount', function (): void {
            $entity = UserDwsSubsidy::create([
                'subsidyType' => UserDwsSubsidyType::benefitAmount(),
                'benefitAmount' => 3000,
            ] + $this->values);
            $this->assertSame(2000, $entity->compute(2000, 10000));
        });
    }
}
