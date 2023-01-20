<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations;

use App\Validations\CustomValidator;
use Domain\Billing\CopayCoordinationResult;
use Domain\Billing\DwsBillingServiceReport;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatementCopayCoordinationStatus;
use Domain\Billing\DwsBillingStatus;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Billing\LtcsBillingStatus;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsBillingStatementFinderMixin;
use Tests\Unit\Mixins\DwsBillingStatementRepositoryMixin;
use Tests\Unit\Mixins\DwsProvisionReportFinderMixin;
use Tests\Unit\Mixins\GetDwsBillingInfoUseCaseMixin;
use Tests\Unit\Mixins\IdentifyDwsCertificationUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsBillingCopayCoordinationUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsBillingStatementUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\LookupLtcsBillingStatementUseCaseMixin;
use Tests\Unit\Mixins\LookupLtcsBillingUseCaseMixin;
use Tests\Unit\Mixins\LtcsProvisionReportFinderMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\SimpleLookupDwsBillingStatementUseCaseMixin;
use Tests\Unit\Mixins\ValidateCopayCoordinationItemUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\BillingValidator} Test.
 */
class BillingValidatorTest extends Test
{
    use ContextMixin;
    use DwsBillingStatementFinderMixin;
    use DwsBillingStatementRepositoryMixin;
    use DwsProvisionReportFinderMixin;
    use ExamplesConsumer;
    use GetDwsBillingInfoUseCaseMixin;
    use IdentifyDwsCertificationUseCaseMixin;
    use LookupDwsBillingUseCaseMixin;
    use LookupDwsBillingBundleUseCaseMixin;
    use LookupDwsBillingStatementUseCaseMixin;
    use LookupDwsBillingCopayCoordinationUseCaseMixin;
    use LookupLtcsBillingStatementUseCaseMixin;
    use LookupLtcsBillingUseCaseMixin;
    use LtcsProvisionReportFinderMixin;
    use MockeryMixin;
    use SimpleLookupDwsBillingStatementUseCaseMixin;
    use UnitSupport;
    use ValidateCopayCoordinationItemUseCaseMixin;

    private Permission $permission;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (BillingValidatorTest $self) {
            $self->validateCopayCoordinationItemUseCase
                ->allows('handle')
                ->andReturn(true)
                ->byDefault();

            $self->permission = Permission::createBillings();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateDwsBillingStatementCopayCoordinationStatusCanUpdate(): void
    {
        $this->should('pass when status is invalid', function (): void {
            $this->assertTrue(
                CustomValidator::make(
                    $this->context,
                    [
                        'billingId' => $this->examples->dwsBillings[0]->id,
                        'billingBundleId' => $this->examples->dwsBillingBundles[0]->id,
                        'id' => $this->examples->dwsBillingStatements[0]->id,
                        'status' => self::INVALID_ENUM_VALUE,
                    ],
                    ['status' => 'dws_billing_statement_copay_coordination_status_can_update'],
                    [],
                    []
                )
                    ->passes()
            );
        });
        $this->should('pass when billingId is empty', function (): void {
            $this->assertTrue(
                CustomValidator::make(
                    $this->context,
                    [
                        'billingId' => '',
                        'billingBundleId' => $this->examples->dwsBillingBundles[0]->id,
                        'id' => $this->examples->dwsBillingStatements[0]->id,
                        'status' => DwsBillingStatementCopayCoordinationStatus::fulfilled()->value(),
                    ],
                    ['status' => 'dws_billing_statement_copay_coordination_status_can_update'],
                    [],
                    []
                )
                    ->passes()
            );
        });
        $this->should('pass when billingBundleId is empty', function (): void {
            $this->assertTrue(
                CustomValidator::make(
                    $this->context,
                    [
                        'billingId' => $this->examples->dwsBillings[0]->id,
                        'billingBundleId' => '',
                        'id' => $this->examples->dwsBillingStatements[0]->id,
                        'status' => DwsBillingStatementCopayCoordinationStatus::fulfilled()->value(),
                    ],
                    ['status' => 'dws_billing_statement_copay_coordination_status_can_update'],
                    [],
                    []
                )
                    ->passes()
            );
        });
        $this->should('pass when id is empty', function (): void {
            $this->assertTrue(
                CustomValidator::make(
                    $this->context,
                    [
                        'billingId' => $this->examples->dwsBillings[0]->id,
                        'billingBundleId' => $this->examples->dwsBillingBundles[0]->id,
                        'id' => '',
                        'status' => DwsBillingStatementCopayCoordinationStatus::fulfilled()->value(),
                    ],
                    ['status' => 'dws_billing_statement_copay_coordination_status_can_update'],
                    [],
                    []
                )
                    ->passes()
            );
        });
        $this->should('pass when lookupDwsBillingStatementUseCase return empty', function (): void {
            $this->lookupDwsBillingStatementUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());
            $this->assertTrue(
                CustomValidator::make(
                    $this->context,
                    [
                        'billingId' => $this->examples->dwsBillings[0]->id,
                        'billingBundleId' => $this->examples->dwsBillingBundles[0]->id,
                        'id' => $this->examples->dwsBillingStatements[0]->id,
                        'status' => DwsBillingStatementCopayCoordinationStatus::fulfilled()->value(),
                    ],
                    ['status' => 'dws_billing_statement_copay_coordination_status_can_update'],
                    [],
                    []
                )
                    ->passes()
            );
        });
        $this->should(
            'pass when original copayCoordinationStatus is uncreated and input status is unclaimable',
            function (): void {
                $this->lookupDwsBillingStatementUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from($this->examples->dwsBillingStatements[11]));
                $this->assertTrue(
                    CustomValidator::make(
                        $this->context,
                        [
                            'billingId' => $this->examples->dwsBillings[0]->id,
                            'billingBundleId' => $this->examples->dwsBillingBundles[0]->id,
                            'id' => $this->examples->dwsBillingStatements[0]->id,
                            'status' => DwsBillingStatementCopayCoordinationStatus::unclaimable()->value(),
                        ],
                        ['status' => 'dws_billing_statement_copay_coordination_status_can_update'],
                        [],
                        []
                    )
                        ->passes()
                );
            }
        );
        $this->should('fail when original copayCoordinationStatus is not uncreated', function (): void {
            $this->lookupDwsBillingStatementUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->dwsBillingStatements[0]));
            $this->assertTrue(
                CustomValidator::make(
                    $this->context,
                    [
                        'billingId' => $this->examples->dwsBillings[0]->id,
                        'billingBundleId' => $this->examples->dwsBillingBundles[0]->id,
                        'id' => $this->examples->dwsBillingStatements[0]->id,
                        'status' => DwsBillingStatementCopayCoordinationStatus::unclaimable()->value(),
                    ],
                    ['status' => 'dws_billing_statement_copay_coordination_status_can_update'],
                    [],
                    []
                )
                    ->fails()
            );
        });
        $this->should('fail when input status is not unclaimable', function (): void {
            $this->lookupDwsBillingStatementUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->dwsBillingStatements[11]));
            $this->assertTrue(
                CustomValidator::make(
                    $this->context,
                    [
                        'billingId' => $this->examples->dwsBillings[0]->id,
                        'billingBundleId' => $this->examples->dwsBillingBundles[0]->id,
                        'id' => $this->examples->dwsBillingStatements[0]->id,
                        'status' => DwsBillingStatementCopayCoordinationStatus::fulfilled()->value(),
                    ],
                    ['status' => 'dws_billing_statement_copay_coordination_status_can_update'],
                    [],
                    []
                )
                    ->fails()
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateItemsHaveIntegrityOfResult(): void
    {
        $this->should('return true when result is invalid', function (): void {
            $this->assertTrue(
                CustomValidator::make(
                    $this->context,
                    [
                        // 関連パラメータ
                        'dwsBillingBundleId' => $this->examples->dwsBillingBundles[1]->id,
                        'dwsBillingId' => $this->examples->dwsBillings[0]->id,
                        'result' => 12345,
                        'userId' => self::NOT_EXISTING_ID,
                        // テストデータ
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
                            'items_have_integrity_of_result:result,userId,dwsBillingId,dwsBillingBundleId,'
                            . $this->permission->value(),
                        ],
                    ],
                    [],
                    []
                )
                    ->passes()
            );
        });
        $this->should('return true when items is not array', function (): void {
            $this->assertTrue(
                CustomValidator::make(
                    $this->context,
                    [
                        // 関連パラメータ
                        'dwsBillingBundleId' => $this->examples->dwsBillingBundles[1]->id,
                        'dwsBillingId' => $this->examples->dwsBillings[0]->id,
                        'result' => CopayCoordinationResult::notCoordinated()->value(),
                        'userId' => self::NOT_EXISTING_ID,
                        // テストデータ
                        'items' => 'error',
                    ],
                    [
                        'items' => [
                            'items_have_integrity_of_result:result,userId,dwsBillingId,dwsBillingBundleId,'
                            . $this->permission->value(),
                        ],
                    ],
                    [],
                    []
                )
                    ->passes()
            );
        });
        $this->should('return true when ValidateCopayCoordinationItemUseCase return true', function (): void {
            $items = [
                [
                    'subtotal' => [
                        'copay' => 0,
                        'coordinatedCopay' => 0,
                    ],
                    'officeId' => $this->examples->dwsBillings[0]->office->officeId,
                ],
            ];
            $resultValue = CopayCoordinationResult::notCoordinated()->value();
            $userId = self::NOT_EXISTING_ID;
            $this->validateCopayCoordinationItemUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    equalTo(Seq::fromArray($items)),
                    CopayCoordinationResult::from($resultValue),
                    $userId,
                    $this->examples->dwsBillings[0]->id,
                    $this->examples->dwsBillingBundles[1]->id,
                    $this->permission
                )
                ->andReturn(true);

            $this->assertTrue(
                CustomValidator::make(
                    $this->context,
                    [
                        // 関連パラメータ
                        'dwsBillingBundleId' => $this->examples->dwsBillingBundles[1]->id,
                        'dwsBillingId' => $this->examples->dwsBillings[0]->id,
                        'result' => $resultValue,
                        'userId' => $userId,
                        // テストデータ
                        'items' => $items,
                    ],
                    [
                        'items' => [
                            'items_have_integrity_of_result:result,userId,dwsBillingId,dwsBillingBundleId,'
                            . $this->permission->value(),
                        ],
                    ],
                    [],
                    []
                )
                    ->passes()
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateLtcsBillingStatementStatusCanBulkUpdate(): void
    {
        $customValidator = function (int $status, array $dataOverwrite = []): CustomValidator {
            return CustomValidator::make(
                $this->context,
                $dataOverwrite + [
                    'billingId' => $this->examples->ltcsBillingStatements[0]->billingId,
                    'billingBundleId' => $this->examples->ltcsBillingStatements[0]->bundleId,
                    'ids' => [$this->examples->ltcsBillingStatements[0]->id],
                    'status' => $status,
                ],
                [
                    'ids' => 'ltcs_billing_statement_status_can_bulk_update:status',
                ],
                [],
                []
            );
        };
        $this->should('pass when status is invalid', function () use ($customValidator): void {
            $status = self::INVALID_ENUM_VALUE;
            $this->assertTrue($customValidator($status)->passes());
        });
        $this->should('pass when billingId is invalid', function () use ($customValidator): void {
            $status = LtcsBillingStatus::fixed()->value();
            $billingId = 'error';
            $this->assertTrue($customValidator($status, compact('billingId'))->passes());
        });
        $this->should('pass when billingBundleId is invalid', function () use ($customValidator): void {
            $status = LtcsBillingStatus::fixed()->value();
            $billingBundleId = 'error';
            $this->assertTrue($customValidator($status, compact('billingBundleId'))->passes());
        });
        $this->should('pass when ids is not array', function () use ($customValidator): void {
            $status = LtcsBillingStatus::fixed()->value();
            $ids = 1;
            $this->assertTrue($customValidator($status, compact('ids'))->passes());
        });
        $this->should('fail when billing is not found', function () use ($customValidator): void {
            $status = LtcsBillingStatus::fixed()->value();
            $this->lookupLtcsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());

            $this->assertTrue($customValidator($status)->fails());
        });
        $this->should('fail when statements are not found', function () use ($customValidator): void {
            $status = LtcsBillingStatus::fixed()->value();
            $this->lookupLtcsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->ltcsBillings[0]));
            $this->lookupLtcsBillingStatementUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());
            $this->assertTrue($customValidator($status)->fails());
        });
        $this->should('fail when billing status is fixed', function () use ($customValidator): void {
            $status = LtcsBillingStatus::fixed()->value();
            $this->lookupLtcsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->ltcsBillings[0]->copy(['status' => LtcsBillingStatus::fixed()])));
            $this->lookupLtcsBillingStatementUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->ltcsBillingStatements[0]));

            $this->assertTrue($customValidator($status)->fails());
        });
        $this->should(
            'fail when status is fixed although current status is not ready',
            function () use ($customValidator): void {
                $status = LtcsBillingStatus::fixed()->value();
                $this->lookupLtcsBillingUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from($this->examples->ltcsBillings[0]->copy(['status' => LtcsBillingStatus::checking()])));
                $this->lookupLtcsBillingStatementUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from($this->examples->ltcsBillingStatements[0]->copy(['status' => LtcsBillingStatus::checking()])));

                $this->assertTrue($customValidator($status)->fails());
            }
        );
        $this->should(
            'fail when status is ready although current status is not fixed',
            function () use ($customValidator): void {
                $status = LtcsBillingStatus::ready()->value();
                $this->lookupLtcsBillingUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from($this->examples->ltcsBillings[0]->copy(['status' => LtcsBillingStatus::checking()])));
                $this->lookupLtcsBillingStatementUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from($this->examples->ltcsBillingStatements[0]->copy(['status' => LtcsBillingStatus::checking()])));

                $this->assertTrue($customValidator($status)->fails());
            }
        );
        $this->should(
            'pass when status is fixed and current status is ready',
            function () use ($customValidator): void {
                $status = LtcsBillingStatus::fixed()->value();
                $this->lookupLtcsBillingUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from($this->examples->ltcsBillings[0]->copy(['status' => LtcsBillingStatus::checking()])));
                $this->lookupLtcsBillingStatementUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from($this->examples->ltcsBillingStatements[0]->copy(['status' => LtcsBillingStatus::ready()])));

                $this->assertTrue($customValidator($status)->passes());
            }
        );
        $this->should(
            'pass when status is ready and current status is fixed',
            function () use ($customValidator): void {
                $status = LtcsBillingStatus::ready()->value();
                $this->lookupLtcsBillingUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from($this->examples->ltcsBillings[0]->copy(['status' => LtcsBillingStatus::checking()])));
                $this->lookupLtcsBillingStatementUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from($this->examples->ltcsBillingStatements[0]->copy(['status' => LtcsBillingStatus::fixed()])));

                $this->assertTrue($customValidator($status)->passes());
            }
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateLtcsBillingStatementStatusCanUpdate(): void
    {
        $customValidator = function (int $status, array $dataOverwrite = []): CustomValidator {
            return CustomValidator::make(
                $this->context,
                $dataOverwrite + [
                    'billingId' => $this->examples->ltcsBillingStatements[0]->billingId,
                    'billingBundleId' => $this->examples->ltcsBillingStatements[0]->bundleId,
                    'id' => $this->examples->ltcsBillingStatements[0]->id,
                    'status' => $status,
                ],
                [
                    'status' => 'ltcs_billing_statement_status_can_update',
                ],
                [],
                []
            );
        };
        $this->should(
            'return true when status can update',
            function (LtcsBillingStatus $original, LtcsBillingStatus $updated) use ($customValidator): void {
                $this->lookupLtcsBillingUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from($this->examples->ltcsBillings[0]->copy([
                        'status' => LtcsBillingStatus::ready(),
                    ])));
                $this->lookupLtcsBillingStatementUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from(
                        $this->examples->ltcsBillingStatements[0]->copy(
                            [
                                'status' => $original,
                            ]
                        )
                    ));

                $this->assertTrue($customValidator($updated->value())->passes());
            },
            [
                'examples' => [
                    'fixed' => [LtcsBillingStatus::ready(), LtcsBillingStatus::fixed()],
                    'ready' => [LtcsBillingStatus::fixed(), LtcsBillingStatus::ready()],
                ],
            ]
        );
        $this->should('return false when status cannot update', function () use ($customValidator): void {
            $this->lookupLtcsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->ltcsBillings[0]->copy([
                    'status' => LtcsBillingStatus::ready(),
                ])));
            $this->lookupLtcsBillingStatementUseCase
                ->expects('handle')
                ->andReturn(Seq::from(
                    $this->examples->ltcsBillingStatements[0]->copy(
                        [
                            'status' => LtcsBillingStatus::ready(),
                        ]
                    )
                ));

            $this->assertTrue($customValidator(LtcsBillingStatus::checking()->value())->fails());
        });
        $this->should('return true when status is invalid', function () use ($customValidator): void {
            $this->lookupLtcsBillingUseCase
                ->allows('handle')
                ->times(0);
            $this->lookupLtcsBillingStatementUseCase
                ->allows('handle')
                ->times(0);

            $this->assertTrue($customValidator(self::INVALID_ENUM_VALUE)->passes());
        });
        $this->should('return true when id not exists', function () use ($customValidator): void {
            $this->lookupLtcsBillingUseCase
                ->allows('handle')
                ->times(0);
            $this->lookupLtcsBillingStatementUseCase
                ->allows('handle')
                ->times(0);

            $this->assertTrue($customValidator(LtcsBillingStatus::fixed()->value(), ['id' => null])->passes());
        });
        $this->should('return true when LookupUseCase return empty', function () use ($customValidator): void {
            $this->lookupLtcsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->ltcsBillings[0]->copy([
                    'status' => LtcsBillingStatus::ready(),
                ])));
            $this->lookupLtcsBillingStatementUseCase
                ->expects('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertTrue($customValidator(LtcsBillingStatus::fixed()->value())->passes());
        });
        $this->should('return false when billing is fixed', function () use ($customValidator): void {
            $this->lookupLtcsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->ltcsBillings[0]->copy([
                    'status' => LtcsBillingStatus::fixed(),
                ])));
            $this->lookupLtcsBillingStatementUseCase
                ->expects('handle')
                ->andReturn(Seq::from(
                    $this->examples->ltcsBillingStatements[0]->copy(
                        [
                            'status' => LtcsBillingStatus::fixed(),
                        ]
                    )
                ));

            $this->assertTrue($customValidator(LtcsBillingStatus::ready()->value())->fails());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateLtcsBillingStatusCanUpdate(): void
    {
        $customValidator = function (int $status, array $dataOverwrite = []): CustomValidator {
            return CustomValidator::make(
                $this->context,
                $dataOverwrite + [
                    'id' => $this->examples->ltcsBillings[0]->id,
                    'status' => $status,
                ],
                [
                    'status' => 'ltcs_billing_status_can_update',
                ],
                [],
                []
            );
        };
        $this->should('return true when status can update', function () use ($customValidator): void {
            $this->lookupLtcsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->ltcsBillings[0]->copy(['status' => LtcsBillingStatus::ready()])));

            $this->assertTrue($customValidator(LtcsBillingStatus::fixed()->value())->passes());
        });
        $this->should('return false when status cannot update', function () use ($customValidator): void {
            $this->lookupLtcsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->ltcsBillings[0]->copy(['status' => LtcsBillingStatus::ready()])));

            $this->assertTrue($customValidator(LtcsBillingStatus::checking()->value())->fails());
        });
        $this->should('return true when status is invalid', function () use ($customValidator): void {
            $this->lookupLtcsBillingUseCase
                ->allows('handle')
                ->times(0);

            $this->assertTrue($customValidator(self::INVALID_ENUM_VALUE)->passes());
        });
        $this->should('return true when id not exists', function () use ($customValidator): void {
            $this->lookupLtcsBillingUseCase
                ->allows('handle')
                ->times(0);

            $this->assertTrue($customValidator(LtcsBillingStatus::fixed()->value(), ['id' => null])->passes());
        });
        $this->should('return true when LookupUseCase return empty', function () use ($customValidator): void {
            $this->lookupLtcsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertTrue($customValidator(LtcsBillingStatus::fixed()->value())->passes());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateDwsProvisionReportExists(): void
    {
        $customValidator = function (): CustomValidator {
            return CustomValidator::make(
                $this->context,
                [
                    'officeId' => $this->examples->offices[1]->id,
                    'transactedIn' => '2021-05',
                ],
                [
                    'officeId' => 'dws_provision_report_exists:transactedIn',
                ],
                [],
                []
            );
        };
        $this->should(
            'return false when UseCase return reports that have plans other than own expense',
            function () use ($customValidator): void {
                $reports = [$this->examples->dwsProvisionReports[14], $this->examples->dwsProvisionReports[16]];
                $this->dwsProvisionReportFinder
                    ->allows('find')
                    ->andReturn(FinderResult::from(Seq::fromArray($reports), Pagination::create()));
                $this->identifyDwsCertificationUseCase
                    ->allows('handle')
                    ->andReturn(Option::from($this->examples->dwsCertifications[23]));
                $this->assertTrue($customValidator()->fails());
            }
        );
        $this->should(
            'return true when UseCase return reports that have results other than own expense',
            function () use ($customValidator): void {
                $reports = [$this->examples->dwsProvisionReports[15], $this->examples->dwsProvisionReports[16]];
                $this->dwsProvisionReportFinder
                    ->allows('find')
                    ->andReturn(FinderResult::from(Seq::fromArray($reports), Pagination::create()));
                $this->identifyDwsCertificationUseCase
                    ->expects('handle')
                    ->never();
                $this->assertTrue($customValidator()->passes());
            }
        );
        $this->should(
            'return true when UseCase return reports that only have own expense but copay coordination type is internal',
            function () use ($customValidator): void {
                $report = $this->examples->dwsProvisionReports[16];
                $this->dwsProvisionReportFinder
                    ->allows('find')
                    ->andReturn(FinderResult::from(Seq::from($report), Pagination::create()));
                $this->identifyDwsCertificationUseCase
                    ->allows('handle')
                    ->andReturn(Option::from($this->examples->dwsCertifications[22]));
                $this->assertTrue($customValidator()->passes());
            }
        );
        $this->should(
            'return false when UseCase return reports that only have own expense and copay coordination type is external',
            function () use ($customValidator): void {
                $report = $this->examples->dwsProvisionReports[16];
                $this->dwsProvisionReportFinder
                    ->allows('find')
                    ->andReturn(FinderResult::from(Seq::from($report), Pagination::create()));
                $this->identifyDwsCertificationUseCase
                    ->allows('handle')
                    ->andReturn(Option::from($this->examples->dwsCertifications[23]));
                $this->assertTrue($customValidator()->fails());
            }
        );
        $this->should(
            'return false when UseCase return reports that only plans and copay coordination type is external',
            function () use ($customValidator): void {
                $report = $this->examples->dwsProvisionReports[14];
                $this->dwsProvisionReportFinder
                    ->allows('find')
                    ->andReturn(FinderResult::from(Seq::from($report), Pagination::create()));
                $this->identifyDwsCertificationUseCase
                    ->allows('handle')
                    ->andReturn(Option::from($this->examples->dwsCertifications[23]));
                $this->assertTrue($customValidator()->fails());
            }
        );
        $this->should('return false when UseCase return empty', function () use ($customValidator): void {
            $this->dwsProvisionReportFinder
                ->allows('find')
                ->andReturn(FinderResult::from(Seq::empty(), Pagination::create()));
            $this->assertTrue($customValidator()->fails());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateDwsServiceDivisionCodeExists(): void
    {
        $customValidator = function (DwsServiceDivisionCode $code): CustomValidator {
            return CustomValidator::make(
                $this->context,
                [
                    'billingId' => $this->examples->dwsBillings[0]->id,
                    'billingBundleId' => $this->examples->dwsBillingBundles[1]->id,
                    'id' => $this->examples->dwsBillingStatements[2]->id,
                    'aggregates' => [
                        ['serviceDivisionCode' => $code->value()],
                    ],
                ],
                [
                    'aggregates' => 'dws_service_division_code_exists:id,billingId,billingBundleId,' . Permission::updateBillings(),
                ],
                [],
                []
            );
        };
        $this->should('return true when UseCase return empty', function () use ($customValidator): void {
            $this->lookupDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(Seq::emptySeq());
            $this->assertTrue(
                $customValidator(DwsServiceDivisionCode::homeHelpService())->passes()
            );
        });
        $this->should('return true when ServiceDivisionCode exists', function () use ($customValidator): void {
            $this->lookupDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(Seq::from($this->examples->dwsBillingStatements[0]));
            $serviceDivisionCode = $this->examples->dwsBillingStatements[0]->aggregates[0]->serviceDivisionCode;
            $this->assertTrue($customValidator($serviceDivisionCode)->passes());
        });
        $this->should('return false when ServiceDivisionCode not exists', function () use ($customValidator): void {
            $this->lookupDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(Seq::from($this->examples->dwsBillingStatements[0]->copy([
                    'aggregates' => [
                        $this->examples->dwsBillingStatements[0]->aggregates[0]->copy([
                            'serviceDivisionCode' => DwsServiceDivisionCode::homeHelpService(),
                        ]),
                    ],
                ])));

            $this->assertTrue($customValidator(DwsServiceDivisionCode::visitingCareForPwsd())->fails());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateDwsBillingCopayCoordinationCanUpdate(): void
    {
        $customValidator = function (array $dataOverwrite = []): CustomValidator {
            return CustomValidator::make(
                $this->context,
                $dataOverwrite + [
                    'id' => $this->examples->dwsBillingCopayCoordinations[0]->id,
                ],
                [
                    'id' => 'dws_billing_copay_coordination_can_update',
                ],
                [],
                []
            );
        };
        $this->should(
            'return false when cannot update',
            function (array $overwrite) use ($customValidator): void {
                $this->dwsBillingStatementFinder
                    ->allows('find')
                    ->andReturn(
                        FinderResult::from(
                            [
                                $this->examples->dwsBillingStatements[0]->copy($overwrite + [
                                    'status' => DwsBillingStatus::checking(),
                                    'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::unfilled(),
                                ]),
                            ],
                            Pagination::create()
                        )
                    );

                $this->assertTrue($customValidator()->fails());
            },
            [
                'examples' => [
                    'when status is fixed' => [['status' => DwsBillingStatus::fixed()]],
                    'when copayCoordinationStatus is unapplicable' => [['copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::unapplicable()]],
                    'when copayCoordinationStatus is unclaimable' => [['copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::unclaimable()]],
                    'when copayCoordinationStatus is fulfilled' => [['copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::fulfilled()]],
                ],
            ]
        );
        $this->should(
            'return true when DwsBillingStatementFinder returns empty list',
            function () use ($customValidator): void {
                $this->dwsBillingStatementFinder
                    ->allows('find')
                    ->andReturn(
                        FinderResult::from(
                            [],
                            Pagination::create()
                        )
                    );

                $this->assertTrue($customValidator()->passes());
            }
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateDwsBillingCopayCoordinationStatusCanUpdateForBillingStatus(): void
    {
        $customValidator = function (int $status, array $dataOverwrite = []): CustomValidator {
            return CustomValidator::make(
                $this->context,
                $dataOverwrite + [
                    'dwsBillingId' => $this->examples->dwsBillingCopayCoordinations[0]->dwsBillingId,
                    'dwsBillingBundleId' => $this->examples->dwsBillingCopayCoordinations[0]->dwsBillingBundleId,
                    'id' => $this->examples->dwsBillingCopayCoordinations[0]->id,
                    'status' => $status,
                ],
                [
                    'status' => 'dws_billing_copay_coordination_status_can_update_for_billing_status',
                ],
                [],
                []
            );
        };
        $this->should(
            'return false when status cannot update',
            function (array $overwrite, ?DwsBillingStatus $updatedStatus = null) use ($customValidator) {
                $this->lookupDwsBillingCopayCoordinationUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from(
                        $this->examples->dwsBillingCopayCoordinations[0]->copy(
                            $overwrite + [
                                'status' => DwsBillingStatus::ready(),
                            ]
                        )
                    ));
                $this->dwsBillingStatementFinder
                    ->expects('find')
                    ->times(0);

                $this->assertTrue($customValidator(($updatedStatus ?? DwsBillingStatus::fixed())->value())->fails());
            },
            [
                'examples' => [
                    'when original status is ready and updated status is checking' => [
                        ['status' => DwsBillingStatus::ready()],
                        DwsBillingStatus::checking(),
                    ],
                ],
            ]
        );
        $this->should(
            'return true when status can update',
            function (array $overwrite, ?DwsBillingStatus $updatedStatus = null) use ($customValidator) {
                $this->lookupDwsBillingCopayCoordinationUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from($this->examples->dwsBillingCopayCoordinations[0]->copy($overwrite)));
                $this->dwsBillingStatementFinder
                    ->expects('find')
                    ->andReturn(FinderResult::from(
                        [
                            $this->examples->dwsBillingStatements[0]->copy([
                                'user' => $this->examples->dwsBillingCopayCoordinations[0]->user,
                                'status' => DwsBillingStatus::ready(),
                            ]),
                        ],
                        Pagination::create()
                    ));

                $this->assertTrue($customValidator(($updatedStatus ?? DwsBillingStatus::fixed())->value())->passes());
            },
            [
                'examples' => [
                    'when original status is ready and updated status is fixed' => [
                        ['status' => DwsBillingStatus::ready()],
                        DwsBillingStatus::fixed(),
                    ],
                    'when original status is fixed and updated status is ready' => [
                        ['status' => DwsBillingStatus::fixed()],
                        DwsBillingStatus::ready(),
                    ],
                ],
            ]
        );
        $this->should('return true when status is invalid', function () use ($customValidator): void {
            $this->lookupDwsBillingCopayCoordinationUseCase
                ->expects('handle')
                ->times(0);
            $this->dwsBillingStatementFinder
                ->expects('find')
                ->times(0);

            $this->assertTrue($customValidator(self::INVALID_ENUM_VALUE)->passes());
        });
        $this->should('return true when id not exists', function () use ($customValidator): void {
            $this->lookupDwsBillingCopayCoordinationUseCase
                ->expects('handle')
                ->times(0);
            $this->dwsBillingStatementFinder
                ->expects('find')
                ->times(0);

            $this->assertTrue($customValidator(DwsBillingStatus::fixed()->value(), ['id' => null])->passes());
        });
        $this->should('return true when LookupUseCase return empty', function () use ($customValidator): void {
            $this->lookupDwsBillingCopayCoordinationUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());
            $this->dwsBillingStatementFinder
                ->expects('find')
                ->times(0);

            $this->assertTrue($customValidator(DwsBillingStatus::fixed()->value())->passes());
        });
        $this->should('return false when billing is fixed', function () use ($customValidator): void {
            $this->lookupDwsBillingCopayCoordinationUseCase
                ->expects('handle')
                ->andReturn(Seq::from(
                    $this->examples->dwsBillingCopayCoordinations[0]->copy(
                        [
                            'status' => DwsBillingStatus::fixed(),
                        ]
                    )
                ));
            $this->dwsBillingStatementFinder
                ->expects('find')
                ->andReturn(FinderResult::from(
                    [
                        $this->examples->dwsBillingStatements[0]->copy([
                            'user' => $this->examples->dwsBillingCopayCoordinations[0]->user,
                            'status' => DwsBillingStatus::fixed(),
                        ]),
                    ],
                    Pagination::create()
                ));

            $this->assertTrue($customValidator(DwsBillingStatus::ready()->value())->fails());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateDwsBillingStatusCanUpdate(): void
    {
        $customValidator = function (int $status, array $dataOverwrite = []): CustomValidator {
            return CustomValidator::make(
                $this->context,
                $dataOverwrite + [
                    'id' => $this->examples->dwsBillings[0]->id,
                    'status' => $status,
                ],
                [
                    'status' => 'dws_billing_status_can_update',
                ],
                [],
                []
            );
        };
        $this->should('pass when status is invalid', function () use ($customValidator): void {
            $this->getDwsBillingInfoUseCase
                ->expects('handle')
                ->never();

            $this->assertTrue($customValidator(self::INVALID_ENUM_VALUE)->passes());
        });
        $this->should('pass when id does not exist', function () use ($customValidator): void {
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->never();

            $this->assertTrue($customValidator(DwsBillingStatus::fixed()->value(), ['id' => null])->passes());
        });
        $this->should('pass when status is changed from fixed to disabled', function () use ($customValidator): void {
            $billing = $this->examples->dwsBillings[0]->copy(['status' => DwsBillingStatus::fixed()]);
            $bundles = $this->examples->dwsBillingBundles;
            $copayCoordinations = $this->examples->dwsBillingCopayCoordinations;
            $statements = Seq::from(...$this->examples->dwsBillingStatements)
                ->map(fn (DwsBillingStatement $x): DwsBillingStatement => $x->copy([
                    'status' => DwsBillingStatus::fixed(),
                ]))
                ->toArray();
            $reports = Seq::from(...$this->examples->dwsBillingServiceReports)
                ->map(fn (DwsBillingServiceReport $x): DwsBillingServiceReport => $x->copy([
                    'status' => DwsBillingStatus::fixed(),
                ]))
                ->toArray();
            $info = compact('billing', 'bundles', 'copayCoordinations', 'reports', 'statements');
            $this->getDwsBillingInfoUseCase
                ->expects('handle')
                ->andReturn($info);

            $this->assertTrue($customValidator(DwsBillingStatus::disabled()->value())->passes());
        });
        $this->should('pass when status is changed from ready to fixed', function () use ($customValidator): void {
            $billing = $this->examples->dwsBillings[0]->copy(['status' => DwsBillingStatus::ready()]);
            $bundles = $this->examples->dwsBillingBundles;
            $copayCoordinations = $this->examples->dwsBillingCopayCoordinations;
            $statements = Seq::from(...$this->examples->dwsBillingStatements)
                ->map(fn (DwsBillingStatement $x): DwsBillingStatement => $x->copy([
                    'status' => DwsBillingStatus::fixed(),
                ]))
                ->toArray();
            $reports = Seq::from(...$this->examples->dwsBillingServiceReports)
                ->map(fn (DwsBillingServiceReport $x): DwsBillingServiceReport => $x->copy([
                    'status' => DwsBillingStatus::fixed(),
                ]))
                ->toArray();
            $info = compact('billing', 'bundles', 'copayCoordinations', 'reports', 'statements');
            $this->getDwsBillingInfoUseCase
                ->expects('handle')
                ->andReturn($info);

            $this->assertTrue($customValidator(DwsBillingStatus::fixed()->value())->passes());
        });
        $this->should(
            'fail when status is changed from ready to fixed although status cannot be updated for statements',
            function () use ($customValidator): void {
                $billing = $this->examples->dwsBillings[0]->copy(['status' => DwsBillingStatus::ready()]);
                $bundles = $this->examples->dwsBillingBundles;
                $copayCoordinations = $this->examples->dwsBillingCopayCoordinations;
                $statements = Seq::from(...$this->examples->dwsBillingStatements)
                    ->map(fn (DwsBillingStatement $x): DwsBillingStatement => $x->copy([
                        'status' => DwsBillingStatus::fixed(),
                    ]))
                    ->append([
                        $this->examples->dwsBillingStatements[0]->copy(['status' => DwsBillingStatus::ready()]),
                    ])
                    ->toArray();
                $reports = Seq::from(...$this->examples->dwsBillingServiceReports)
                    ->map(fn (DwsBillingServiceReport $x): DwsBillingServiceReport => $x->copy([
                        'status' => DwsBillingStatus::fixed(),
                    ]))
                    ->toArray();
                $info = compact('billing', 'bundles', 'copayCoordinations', 'reports', 'statements');
                $this->getDwsBillingInfoUseCase
                    ->expects('handle')
                    ->andReturn($info);

                $this->assertTrue($customValidator(DwsBillingStatus::fixed()->value())->fails());
            }
        );
        $this->should(
            'fail when status is changed from ready to fixed although status cannot be updated for reports',
            function () use ($customValidator): void {
                $billing = $this->examples->dwsBillings[0]->copy(['status' => DwsBillingStatus::ready()]);
                $bundles = $this->examples->dwsBillingBundles;
                $copayCoordinations = $this->examples->dwsBillingCopayCoordinations;
                $statements = Seq::from(...$this->examples->dwsBillingStatements)
                    ->map(fn (DwsBillingStatement $x): DwsBillingStatement => $x->copy([
                        'status' => DwsBillingStatus::fixed(),
                    ]))
                    ->toArray();
                $reports = Seq::from(...$this->examples->dwsBillingServiceReports)
                    ->map(fn (DwsBillingServiceReport $x): DwsBillingServiceReport => $x->copy([
                        'status' => DwsBillingStatus::fixed(),
                    ]))
                    ->append([
                        $this->examples->dwsBillingServiceReports[0]->copy(['status' => DwsBillingStatus::ready()]),
                    ])
                    ->toArray();
                $info = compact('billing', 'bundles', 'copayCoordinations', 'reports', 'statements');
                $this->getDwsBillingInfoUseCase
                    ->expects('handle')
                    ->andReturn($info);

                $this->assertTrue($customValidator(DwsBillingStatus::fixed()->value())->fails());
            }
        );
        $this->should(
            'fail when status is changed neither from fixed to disabled nor from ready to fixed',
            function () use ($customValidator): void {
                $billing = $this->examples->dwsBillings[0]->copy(['status' => DwsBillingStatus::fixed()]);
                $bundles = $this->examples->dwsBillingBundles;
                $copayCoordinations = $this->examples->dwsBillingCopayCoordinations;
                $statements = Seq::from(...$this->examples->dwsBillingStatements)
                    ->map(fn (DwsBillingStatement $x): DwsBillingStatement => $x->copy([
                        'status' => DwsBillingStatus::fixed(),
                    ]))
                    ->toArray();
                $reports = Seq::from(...$this->examples->dwsBillingServiceReports)
                    ->map(fn (DwsBillingServiceReport $x): DwsBillingServiceReport => $x->copy([
                        'status' => DwsBillingStatus::fixed(),
                    ]))
                    ->toArray();
                $info = compact('billing', 'bundles', 'copayCoordinations', 'reports', 'statements');
                $this->getDwsBillingInfoUseCase
                    ->expects('handle')
                    ->andReturn($info);

                $this->assertTrue($customValidator(DwsBillingStatus::ready()->value())->fails());
            }
        );
        $this->should(
            'fail when status is changed from disabled to disabled',
            function () use ($customValidator): void {
                $billing = $this->examples->dwsBillings[0]->copy(['status' => DwsBillingStatus::disabled()]);
                $bundles = $this->examples->dwsBillingBundles;
                $copayCoordinations = $this->examples->dwsBillingCopayCoordinations;
                $statements = Seq::from(...$this->examples->dwsBillingStatements)
                    ->map(fn (DwsBillingStatement $x): DwsBillingStatement => $x->copy([
                        'status' => DwsBillingStatus::fixed(),
                    ]))
                    ->toArray();
                $reports = Seq::from(...$this->examples->dwsBillingServiceReports)
                    ->map(fn (DwsBillingServiceReport $x): DwsBillingServiceReport => $x->copy([
                        'status' => DwsBillingStatus::fixed(),
                    ]))
                    ->toArray();
                $info = compact('billing', 'bundles', 'copayCoordinations', 'reports', 'statements');
                $this->getDwsBillingInfoUseCase
                    ->expects('handle')
                    ->andReturn($info);

                $this->assertTrue($customValidator(DwsBillingStatus::disabled()->value())->fails());
            }
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateCopayCoordinationResultCanUpdate(): void
    {
        $customValidator = function (array $dataOverwrite = []): CustomValidator {
            return CustomValidator::make(
                $this->context,
                $dataOverwrite + [
                    'id' => $this->examples->dwsBillingStatements[0]->id,
                ],
                [
                    'id' => 'copay_coordination_result_can_update',
                ],
                [],
                []
            );
        };
        $this->should(
            'return false when cannot update',
            function (DwsBillingStatementCopayCoordinationStatus $copayCoordinationStatus) use ($customValidator) {
                $this->dwsBillingStatementRepository
                    ->allows('lookup')
                    ->andReturn(Seq::from($this->examples->dwsBillingStatements[0]->copy([
                        'copayCoordinationStatus' => $copayCoordinationStatus,
                    ])));

                $this->assertTrue($customValidator()->fails());
            },
            [
                'examples' => [
                    'unapplicable' => [DwsBillingStatementCopayCoordinationStatus::unapplicable()],
                    'unclaimable' => [DwsBillingStatementCopayCoordinationStatus::unclaimable()],
                ],
            ]
        );
        $this->should(
            'return true when can update',
            function (DwsBillingStatementCopayCoordinationStatus $copayCoordinationStatus) use ($customValidator) {
                $this->dwsBillingStatementRepository
                    ->allows('lookup')
                    ->andReturn(Seq::from($this->examples->dwsBillingStatements[0]->copy([
                        'copayCoordinationStatus' => $copayCoordinationStatus,
                    ])));

                $this->assertTrue($customValidator()->passes());
            },
            [
                'examples' => [
                    'uncreated' => [DwsBillingStatementCopayCoordinationStatus::uncreated()],
                    'unfilled' => [DwsBillingStatementCopayCoordinationStatus::unfilled()],
                    'fulfilled' => [DwsBillingStatementCopayCoordinationStatus::fulfilled()],
                ],
            ]
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateDwsBillingStatementCanUpdate(): void
    {
        $customValidator = function (array $dataOverwrite = []): CustomValidator {
            return CustomValidator::make(
                $this->context,
                $dataOverwrite + [
                    'id' => $this->examples->dwsBillingStatements[0]->id,
                ],
                [
                    'id' => 'dws_billing_statement_can_update',
                ],
                [],
                []
            );
        };
        $this->should(
            'return false when cannot update',
            function (DwsBillingStatus $status) use ($customValidator) {
                $this->dwsBillingStatementRepository
                    ->allows('lookup')
                    ->andReturn(Seq::from($this->examples->dwsBillingStatements[0]->copy(compact('status'))));

                $this->assertTrue($customValidator()->fails());
            },
            [
                'examples' => [
                    'fixed' => [DwsBillingStatus::fixed()],
                ],
            ]
        );
        $this->should(
            'return true when can update',
            function (DwsBillingStatus $status) use ($customValidator) {
                $this->dwsBillingStatementRepository
                    ->allows('lookup')
                    ->andReturn(Seq::from($this->examples->dwsBillingStatements[0]->copy(compact('status'))));

                $this->assertTrue($customValidator()->passes());
            },
            [
                'examples' => [
                    'checking' => [DwsBillingStatus::checking()],
                    'ready' => [DwsBillingStatus::ready()],
                ],
            ]
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateDwsBillingStatementStatusCanBulkUpdate(): void
    {
        $customValidator = function (int $status, array $dataOverwrite = []): CustomValidator {
            return CustomValidator::make(
                $this->context,
                $dataOverwrite + [
                    'billingId' => $this->examples->dwsBillingStatements[0]->dwsBillingId,
                    'ids' => [$this->examples->dwsBillingStatements[0]->id],
                    'status' => $status,
                ],
                [
                    'ids' => 'dws_billing_statement_status_can_bulk_update:status',
                ],
                [],
                []
            );
        };
        $this->should('pass when status is invalid', function () use ($customValidator): void {
            $status = self::INVALID_ENUM_VALUE;
            $this->assertTrue($customValidator($status)->passes());
        });
        $this->should('pass when billingId is invalid', function () use ($customValidator): void {
            $status = DwsBillingStatus::fixed()->value();
            $billingId = 'error';
            $this->assertTrue($customValidator($status, compact('billingId'))->passes());
        });
        $this->should('pass when ids is not array', function () use ($customValidator): void {
            $status = DwsBillingStatus::fixed()->value();
            $ids = 1;
            $this->assertTrue($customValidator($status, compact('ids'))->passes());
        });
        $this->should('fail when billing is not found', function () use ($customValidator): void {
            $status = DwsBillingStatus::fixed()->value();
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());

            $this->assertTrue($customValidator($status)->fails());
        });
        $this->should('fail when statements are not found', function () use ($customValidator): void {
            $status = DwsBillingStatus::fixed()->value();
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->dwsBillings[0]));
            $this->simpleLookupDwsBillingStatementUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());
            $this->assertTrue($customValidator($status)->fails());
        });
        $this->should('fail when billing status is fixed', function () use ($customValidator): void {
            $status = DwsBillingStatus::fixed()->value();
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->dwsBillings[0]->copy(['status' => DwsBillingStatus::fixed()])));
            $this->simpleLookupDwsBillingStatementUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->dwsBillingStatements[0]));

            $this->assertTrue($customValidator($status)->fails());
        });
        $this->should(
            'fail when status is fixed although current status is not ready',
            function () use ($customValidator): void {
                $status = DwsBillingStatus::fixed()->value();
                $this->lookupDwsBillingUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from($this->examples->dwsBillings[0]->copy(['status' => DwsBillingStatus::checking()])));
                $this->simpleLookupDwsBillingStatementUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from($this->examples->dwsBillingStatements[0]->copy(['status' => DwsBillingStatus::checking()])));

                $this->assertTrue($customValidator($status)->fails());
            }
        );
        $this->should(
            'fail when status is ready although current status is not fixed',
            function () use ($customValidator): void {
                $status = DwsBillingStatus::ready()->value();
                $this->lookupDwsBillingUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from($this->examples->dwsBillings[0]->copy(['status' => DwsBillingStatus::checking()])));
                $this->simpleLookupDwsBillingStatementUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from($this->examples->dwsBillingStatements[0]->copy(['status' => DwsBillingStatus::checking()])));

                $this->assertTrue($customValidator($status)->fails());
            }
        );
        $this->should(
            'fail if the number of ids is different from the number of statements',
            function () use ($customValidator): void {
                $status = DwsBillingStatus::fixed()->value();
                $ids = [$this->examples->dwsBillingStatements[0]->id, $this->examples->dwsBillingStatements[1]->id];
                $this->lookupDwsBillingUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from($this->examples->dwsBillings[0]->copy(['status' => DwsBillingStatus::checking()])));
                $this->simpleLookupDwsBillingStatementUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from($this->examples->dwsBillingStatements[0]->copy(['status' => DwsBillingStatus::checking()])));

                $this->assertTrue($customValidator($status, compact('ids'))->fails());
            }
        );
        $this->should(
            'pass when status is fixed and current status is ready',
            function () use ($customValidator): void {
                $status = DwsBillingStatus::fixed()->value();
                $this->lookupDwsBillingUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from($this->examples->dwsBillings[0]->copy(['status' => DwsBillingStatus::checking()])));
                $this->simpleLookupDwsBillingStatementUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from($this->examples->dwsBillingStatements[0]->copy(['status' => DwsBillingStatus::ready()])));

                $this->assertTrue($customValidator($status)->passes());
            }
        );
        $this->should(
            'pass when status is ready and current status is fixed',
            function () use ($customValidator): void {
                $status = DwsBillingStatus::ready()->value();
                $this->lookupDwsBillingUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from($this->examples->dwsBillings[0]->copy(['status' => DwsBillingStatus::checking()])));
                $this->simpleLookupDwsBillingStatementUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from($this->examples->dwsBillingStatements[0]->copy(['status' => DwsBillingStatus::fixed()])));

                $this->assertTrue($customValidator($status)->passes());
            }
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateDwsBillingStatementStatusCanUpdateForBillingStatus(): void
    {
        $customValidator = function (int $status, array $dataOverwrite = []): CustomValidator {
            return CustomValidator::make(
                $this->context,
                $dataOverwrite + [
                    'billingId' => $this->examples->dwsBillingStatements[0]->dwsBillingId,
                    'billingBundleId' => $this->examples->dwsBillingStatements[0]->dwsBillingBundleId,
                    'id' => $this->examples->dwsBillingStatements[0]->id,
                    'status' => $status,
                ],
                [
                    'status' => 'dws_billing_statement_status_can_update_for_billing_status',
                ],
                [],
                []
            );
        };
        $this->should(
            'return false when status cannot update',
            function (array $overwrite, ?DwsBillingStatus $updatedStatus = null) use ($customValidator) {
                $this->lookupDwsBillingUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from($this->examples->dwsBillings[0]->copy([
                        'status' => DwsBillingStatus::ready(),
                    ])));
                $this->lookupDwsBillingStatementUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from(
                        $this->examples->dwsBillingStatements[0]->copy(
                            $overwrite + [
                                'status' => DwsBillingStatus::ready(),
                                'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::unapplicable(),
                            ]
                        )
                    ));

                $this->assertTrue($customValidator(($updatedStatus ?? DwsBillingStatus::fixed())->value())->fails());
            },
            [
                'examples' => [
                    'when original status is ready and updated status is checking' => [
                        ['status' => DwsBillingStatus::ready()],
                        DwsBillingStatus::checking(),
                    ],
                ],
            ]
        );
        $this->should(
            'return true when status can update',
            function (array $overwrite, ?DwsBillingStatus $updatedStatus = null) use ($customValidator) {
                $this->lookupDwsBillingUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from($this->examples->dwsBillings[0]->copy([
                        'status' => DwsBillingStatus::ready(),
                    ])));
                $this->lookupDwsBillingStatementUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from(
                        $this->examples->dwsBillingStatements[0]->copy(
                            $overwrite + [
                                'status' => DwsBillingStatus::ready(),
                                'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::unapplicable(),
                            ]
                        )
                    ));

                $this->assertTrue($customValidator(($updatedStatus ?? DwsBillingStatus::fixed())->value())->passes());
            },
            [
                'examples' => [
                    'when original status is ready and updated status is fixed' => [
                        ['status' => DwsBillingStatus::ready()],
                        DwsBillingStatus::fixed(),
                    ],
                    'when original status is fixed and updated status is ready' => [
                        ['status' => DwsBillingStatus::fixed()],
                        DwsBillingStatus::ready(),
                    ],
                ],
            ]
        );
        $this->should('return true when status is invalid', function () use ($customValidator): void {
            $this->lookupDwsBillingUseCase
                ->allows('handle')
                ->times(0);
            $this->lookupDwsBillingStatementUseCase
                ->allows('handle')
                ->times(0);

            $this->assertTrue($customValidator(self::INVALID_ENUM_VALUE)->passes());
        });
        $this->should('return true when id not exists', function () use ($customValidator): void {
            $this->lookupDwsBillingUseCase
                ->allows('handle')
                ->times(0);
            $this->lookupDwsBillingStatementUseCase
                ->allows('handle')
                ->times(0);

            $this->assertTrue($customValidator(DwsBillingStatus::fixed()->value(), ['id' => null])->passes());
        });
        $this->should('return true when LookupUseCase return empty', function () use ($customValidator): void {
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->dwsBillings[0]->copy([
                    'status' => DwsBillingStatus::ready(),
                ])));
            $this->lookupDwsBillingStatementUseCase
                ->expects('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertTrue($customValidator(DwsBillingStatus::fixed()->value())->passes());
        });
        $this->should('return false when billing is fixed', function () use ($customValidator): void {
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->dwsBillings[0]->copy([
                    'status' => DwsBillingStatus::fixed(),
                ])));
            $this->lookupDwsBillingStatementUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->dwsBillingStatements[0]));

            $this->assertTrue($customValidator(DwsBillingStatus::ready()->value())->fails());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateDwsBillingStatementStatusCanUpdate(): void
    {
        $customValidator = function (int $status, array $dataOverwrite = []): CustomValidator {
            return CustomValidator::make(
                $this->context,
                $dataOverwrite + [
                    'billingId' => $this->examples->dwsBillingStatements[0]->dwsBillingId,
                    'billingBundleId' => $this->examples->dwsBillingStatements[0]->dwsBillingBundleId,
                    'id' => $this->examples->dwsBillingStatements[0]->id,
                    'status' => $status,
                ],
                [
                    'status' => 'dws_billing_statement_status_can_update_for_copay_coordination_status',
                ],
                [],
                []
            );
        };
        $this->should(
            'return false when status cannot update',
            function (array $overwrite, ?DwsBillingStatus $updatedStatus = null) use ($customValidator) {
                $this->lookupDwsBillingStatementUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from(
                        $this->examples->dwsBillingStatements[0]->copy(
                            $overwrite + [
                                'status' => DwsBillingStatus::ready(),
                                'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::unapplicable(),
                            ]
                        )
                    ));

                $this->assertTrue($customValidator(($updatedStatus ?? DwsBillingStatus::fixed())->value())->fails());
            },
            [
                'examples' => [
                    'when copayCoordinationStatus is uncreated' => [['copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::uncreated()]],
                    'when copayCoordinationStatus is unfilled' => [['copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::unfilled()]],
                    'when copayCoordinationStatus is checking' => [['copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::checking()]],
                ],
            ]
        );
        $this->should(
            'return true when status can update',
            function (array $overwrite, ?DwsBillingStatus $updatedStatus = null) use ($customValidator) {
                $this->lookupDwsBillingStatementUseCase
                    ->expects('handle')
                    ->andReturn(Seq::from(
                        $this->examples->dwsBillingStatements[0]->copy(
                            $overwrite + [
                                'status' => DwsBillingStatus::ready(),
                                'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::unapplicable(),
                            ]
                        )
                    ));

                $this->assertTrue($customValidator(($updatedStatus ?? DwsBillingStatus::fixed())->value())->passes());
            },
            [
                'examples' => [
                    'when copayCoordinationStatus is unapplicable' => [['copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::unapplicable()]],
                    'when copayCoordinationStatus is unclaimable' => [['copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::unclaimable()]],
                    'when copayCoordinationStatus is fulfilled' => [['copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::fulfilled()]],
                ],
            ]
        );
        $this->should('return true when status is invalid', function () use ($customValidator): void {
            $this->lookupDwsBillingStatementUseCase
                ->allows('handle')
                ->times(0);

            $this->assertTrue($customValidator(self::INVALID_ENUM_VALUE)->passes());
        });
        $this->should('return true when id not exists', function () use ($customValidator): void {
            $this->lookupDwsBillingStatementUseCase
                ->allows('handle')
                ->times(0);

            $this->assertTrue($customValidator(DwsBillingStatus::fixed()->value(), ['id' => null])->passes());
        });
        $this->should('return true when LookupUseCase return empty', function () use ($customValidator): void {
            $this->lookupDwsBillingStatementUseCase
                ->expects('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertTrue($customValidator(DwsBillingStatus::fixed()->value())->passes());
        });
    }
}
