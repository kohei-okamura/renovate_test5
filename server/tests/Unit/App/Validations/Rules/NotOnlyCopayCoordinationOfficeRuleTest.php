<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use App\Validations\CustomValidator;
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LookupDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\NotOnlyCopayCoordinationOfficeRule} のテスト.
 */
final class NotOnlyCopayCoordinationOfficeRuleTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use LookupDwsBillingUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->lookupDwsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsBillings[0]))
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateNotOnlyCopayCoordinationOffice(): void
    {
        $this->should('pass when isProvided is false', function (): void {
            $this->assertTrue(
                CustomValidator::make(
                    $this->context,
                    [
                        'dwsBillingBundleId' => $this->examples->dwsBillingBundles[0]->id,
                        'dwsBillingId' => $this->examples->dwsBillings[0]->id,
                        'result' => 1,
                        'userId' => $this->examples->users[0]->id,
                        'isProvided' => false,
                        'items' => [
                            [
                                'subtotal' => [
                                    'copay' => 0,
                                    'coordinatedCopay' => 0,
                                ],
                                'officeId' => $this->examples->dwsBillings[0]->office->officeId,
                            ],
                        ],
                    ],
                    [
                        'items' => [
                            'not_only_copay_coordination_office:dwsBillingId,isProvided,' . Permission::createBillings(),
                        ],
                    ],
                )->passes()
            );
        });
        $this->should('pass when value is not Array', function (): void {
            $this->assertTrue(
                CustomValidator::make(
                    $this->context,
                    [
                        'dwsBillingBundleId' => $this->examples->dwsBillingBundles[0]->id,
                        'dwsBillingId' => $this->examples->dwsBillings[0]->id,
                        'result' => 1,
                        'userId' => $this->examples->users[0]->id,
                        'isProvided' => true,
                        'items' => 'hoge',
                    ],
                    [
                        'items' => [
                            'not_only_copay_coordination_office:dwsBillingId,isProvided,' . Permission::createBillings(),
                        ],
                    ],
                )->passes()
            );
        });
        $this->should('pass when value is zero elements', function (): void {
            $this->assertTrue(
                CustomValidator::make(
                    $this->context,
                    [
                        'dwsBillingBundleId' => $this->examples->dwsBillingBundles[0]->id,
                        'dwsBillingId' => $this->examples->dwsBillings[0]->id,
                        'result' => 1,
                        'userId' => $this->examples->users[0]->id,
                        'isProvided' => true,
                        'items' => [],
                    ],
                    [
                        'items' => [
                            'not_only_copay_coordination_office:dwsBillingId,isProvided,' . Permission::createBillings(),
                        ],
                    ],
                )->passes()
            );
        });
        $this->should('pass when DwsBilling is empty', function (): void {
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());
            $this->assertTrue(
                CustomValidator::make(
                    $this->context,
                    [
                        'dwsBillingBundleId' => $this->examples->dwsBillingBundles[0]->id,
                        'dwsBillingId' => $this->examples->dwsBillings[0]->id,
                        'result' => 1,
                        'userId' => $this->examples->users[0]->id,
                        'isProvided' => true,
                        'items' => [
                            [
                                'subtotal' => [
                                    'copay' => 0,
                                    'coordinatedCopay' => 0,
                                ],
                                'officeId' => $this->examples->dwsBillings[0]->office->officeId,
                            ],
                        ],
                    ],
                    [
                        'items' => [
                            'not_only_copay_coordination_office:dwsBillingId,isProvided,' . Permission::createBillings(),
                        ],
                    ],
                )->passes()
            );
        });
        $this->should('fail when only CopayCoordinationOffice', function (): void {
            $this->assertTrue(
                CustomValidator::make(
                    $this->context,
                    [
                        'dwsBillingBundleId' => $this->examples->dwsBillingBundles[0]->id,
                        'dwsBillingId' => $this->examples->dwsBillings[0]->id,
                        'result' => 1,
                        'userId' => $this->examples->users[0]->id,
                        'isProvided' => true,
                        'items' => [
                            [
                                'subtotal' => [
                                    'copay' => 0,
                                    'coordinatedCopay' => 0,
                                ],
                                'officeId' => $this->examples->dwsBillings[0]->office->officeId,
                            ],
                        ],
                    ],
                    [
                        'items' => [
                            'not_only_copay_coordination_office:dwsBillingId,isProvided,' . Permission::createBillings(),
                        ],
                    ],
                )->fails()
            );
        });
        $this->should('pass when multiple offices', function (): void {
            $this->assertTrue(
                CustomValidator::make(
                    $this->context,
                    [
                        'dwsBillingBundleId' => $this->examples->dwsBillingBundles[0]->id,
                        'dwsBillingId' => $this->examples->dwsBillings[0]->id,
                        'result' => 1,
                        'userId' => $this->examples->users[0]->id,
                        'isProvided' => true,
                        'items' => [
                            [
                                'subtotal' => [
                                    'copay' => 0,
                                    'coordinatedCopay' => 0,
                                ],
                                'officeId' => $this->examples->dwsBillings[0]->office->officeId,
                            ],
                            [
                                'subtotal' => [
                                    'copay' => 0,
                                    'coordinatedCopay' => 0,
                                ],
                                'officeId' => $this->examples->dwsBillings[1]->office->officeId,
                            ],
                        ],
                    ],
                    [
                        'items' => [
                            'not_only_copay_coordination_office:dwsBillingId,isProvided,' . Permission::createBillings(),
                        ],
                    ],
                )->passes()
            );
        });
    }
}
