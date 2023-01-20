<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\User;

use Domain\Common\Addr;
use Domain\Common\Prefecture;
use Domain\User\BillingDestination;
use Domain\User\PaymentMethod;
use Domain\User\UserBillingDestination;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \Domain\User\UserBillingDestination} のテスト.
 */
final class UserBillingDestinationTest extends Test
{
    use ExamplesConsumer;
    use UnitSupport;
    use MatchesSnapshots;

    protected UserBillingDestination $userBillingDestination;
    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self) {
            $self->values = [
                'destination' => BillingDestination::agent(),
                'paymentMethod' => PaymentMethod::withdrawal(),
                'contractNumber' => '0123456789',
                'corporationName' => 'ユースタイルラボラトリー株式会社',
                'agentName' => '山田太郎',
                'addr' => new Addr(
                    postcode: '164-0011',
                    prefecture: Prefecture::tokyo(),
                    city: '中野区',
                    street: '中央1-35-6',
                    apartment: 'レッチフィールド中野坂上ビル6F',
                ),
                'tel' => '03-1234-5678',
            ];
            $self->userBillingDestination = UserBillingDestination::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $examples = [
            'destination' => ['destination'],
            'paymentMethod' => ['paymentMethod'],
            'contractNumber' => ['contractNumber'],
            'corporationName' => ['corporationName'],
            'agentName' => ['agentName'],
            'addr' => ['addr'],
            'tel' => ['tel'],
        ];
        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->userBillingDestination->get($key), Arr::get($this->values, $key));
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
            $this->assertMatchesJsonSnapshot($this->userBillingDestination);
        });
    }
}
