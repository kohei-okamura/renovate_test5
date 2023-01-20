<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use App\Validations\CustomValidator;
use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingStatement;
use Domain\Billing\DwsBillingStatus;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Domain\Project\DwsProjectServiceCategory;
use Domain\ProvisionReport\DwsProvisionReportStatus;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\FindDwsProvisionReportUseCaseMixin;
use Tests\Unit\Mixins\IdentifyDwsCertificationUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\LookupDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\SimpleLookupDwsBillingStatementUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\DwsBillingStatementCanRefreshRule} のテスト.
 */
final class DwsBillingStatementCanRefreshRuleTest extends Test
{
    use ExamplesConsumer;
    use FindDwsProvisionReportUseCaseMixin;
    use IdentifyDwsCertificationUseCaseMixin;
    use LookupDwsBillingBundleUseCaseMixin;
    use LookupDwsBillingUseCaseMixin;
    use MockeryMixin;
    use RuleTestSupport;
    use SimpleLookupDwsBillingStatementUseCaseMixin;
    use UnitSupport;

    private DwsBilling $billing;
    private DwsBillingBundle $bundle;
    private Seq $statements;
    private Seq $provisionReports;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->billing = $self->examples->dwsBillings[0];
            $self->bundle = $self->examples->dwsBillingBundles[0];
            $self->statements = Seq::from(
                $self->examples->dwsBillingStatements[0]->copy([
                    'status' => DwsBillingStatus::checking(),
                ]),
                $self->examples->dwsBillingStatements[1]->copy([
                    'status' => DwsBillingStatus::checking(),
                ])
            );
            $self->provisionReports = Seq::from(
                $self->examples->dwsProvisionReports[0]->copy([
                    'status' => DwsProvisionReportStatus::fixed(),
                ]),
                $self->examples->dwsProvisionReports[1]->copy([
                    'status' => DwsProvisionReportStatus::fixed(),
                ]),
            );

            $self->lookupDwsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->billing))
                ->byDefault();
            $self->simpleLookupDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturn($self->statements)
                ->byDefault();
            $self->lookupDwsBillingBundleUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->bundle))
                ->byDefault();
            $self->findDwsProvisionReportUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from($self->provisionReports, Pagination::create()))
                ->byDefault();
            $self->identifyDwsCertificationUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->dwsCertifications[0]))
                ->byDefault();
            $self->identifyDwsCertificationUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->dwsCertifications[1]))
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateDwsBillingStatementCanRefreshRule(): void
    {
        $customValidator = function (array $dataOverwrite = []): CustomValidator {
            $pathParameters = [
                'billingId' => $this->billing->id,
            ];
            return $this->buildCustomValidator(
                $dataOverwrite + $pathParameters + [
                    'ids' => $this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray(),
                ],
                ['ids' => 'dws_billing_statement_can_refresh']
            );
        };
        $this->should('pass when billingId is invalid', function () use ($customValidator): void {
            $billingId = 'error';
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->never();
            $this->simpleLookupDwsBillingStatementUseCase
                ->expects('handle')
                ->never();
            $this->lookupDwsBillingBundleUseCase
                ->expects('handle')
                ->never();
            $this->findDwsProvisionReportUseCase
                ->expects('handle')
                ->never();
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->never();

            $this->assertTrue($customValidator(compact('billingId'))->passes());
        });
        $this->should('pass when ids is not array', function () use ($customValidator): void {
            $ids = 1;
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->never();
            $this->simpleLookupDwsBillingStatementUseCase
                ->expects('handle')
                ->never();
            $this->lookupDwsBillingBundleUseCase
                ->expects('handle')
                ->never();
            $this->findDwsProvisionReportUseCase
                ->expects('handle')
                ->never();
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->never();

            $this->assertTrue($customValidator(compact('ids'))->passes());
        });
        $this->should('fail when LookupDwsBillingUseCase return empty', function () use ($customValidator): void {
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateBillings(), $this->billing->id)
                ->andReturn(Seq::empty());
            $this->simpleLookupDwsBillingStatementUseCase
                ->expects('handle')
                ->never();
            $this->lookupDwsBillingBundleUseCase
                ->expects('handle')
                ->never();
            $this->findDwsProvisionReportUseCase
                ->expects('handle')
                ->never();
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->never();

            $this->assertTrue($customValidator()->fails());
        });
        $this->should('fail when LookupDwsBillingUseCase return fixed billing', function () use ($customValidator): void {
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateBillings(), $this->billing->id)
                ->andReturn(Seq::from($this->billing->copy(['status' => DwsBillingStatus::fixed()])));
            $this->simpleLookupDwsBillingStatementUseCase
                ->expects('handle')
                ->never();
            $this->lookupDwsBillingBundleUseCase
                ->expects('handle')
                ->never();
            $this->findDwsProvisionReportUseCase
                ->expects('handle')
                ->never();
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->never();

            $this->assertTrue($customValidator()->fails());
        });
        $this->should('fail when the number of returned statements are different from the number of ids', function () use ($customValidator): void {
            $this->simpleLookupDwsBillingStatementUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateBillings(),
                    ...$this->statements->map(fn (DwsBillingStatement $x): int => $x->id)->toArray()
                )
                ->andReturn(Seq::from($this->statements[0]));
            $this->lookupDwsBillingBundleUseCase
                ->expects('handle')
                ->never();
            $this->findDwsProvisionReportUseCase
                ->expects('handle')
                ->never();
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->never();

            $this->assertTrue($customValidator()->fails());
        });
        $this->should('fail when bundle is not found', function () use ($customValidator): void {
            $this->lookupDwsBillingBundleUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());
            $this->findDwsProvisionReportUseCase
                ->expects('handle')
                ->never();
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->never();

            $this->assertTrue($customValidator()->fails());
        });
        $this->should('fail when DwsCertifications contain not found', function () use ($customValidator): void {
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->statements[0]->user->userId,
                    $this->bundle->providedIn
                )
                ->andReturn(Option::some($this->examples->dwsCertifications[0]));
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->statements[1]->user->userId,
                    $this->bundle->providedIn
                )
                ->andReturn(Option::none());

            $this->assertTrue($customValidator()->fails());
        });
        $this->should('fail when provisionReport does not exist although coordination is not self', function () use ($customValidator): void {
            $this->findDwsProvisionReportUseCase
                ->expects('handle')
                ->andReturn(FinderResult::from(
                    [
                        $this->provisionReports[0]->copy(['userId' => self::NOT_EXISTING_ID]),
                        $this->provisionReports[1]->copy(['userId' => self::NOT_EXISTING_ID]),
                    ],
                    Pagination::create()
                ));
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->examples->dwsCertifications[0]->copy([
                    'copayCoordination' => $this->examples->dwsCertifications[0]->copayCoordination->copy([
                        'officeId' => 10,
                    ]),
                ])));
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->examples->dwsCertifications[1]->copy([
                    'copayCoordination' => $this->examples->dwsCertifications[1]->copayCoordination->copy([
                        'officeId' => 10,
                    ]),
                ])));

            $this->assertTrue($customValidator()->fails());
        });
        $this->should('pass when provisionReports are fixed and contain dws result', function () use ($customValidator): void {
            $this->findDwsProvisionReportUseCase
                ->expects('handle')
                ->andReturn(FinderResult::from($this->provisionReports, Pagination::create()));
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->examples->dwsCertifications[0]));
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->examples->dwsCertifications[1]));

            $this->assertTrue($customValidator()->passes());
        });
        $this->should('fail when provisionReports contain not fixed', function () use ($customValidator): void {
            $this->findDwsProvisionReportUseCase
                ->expects('handle')
                ->andReturn(
                    FinderResult::from(
                        [
                            $this->provisionReports[0],
                            $this->provisionReports[1]->copy(['status' => DwsProvisionReportStatus::inProgress()]),
                        ],
                        Pagination::create()
                    )
                );

            $this->assertTrue($customValidator()->fails());
        });
        $this->should('fail when provisionReports do not contain dws result', function () use ($customValidator): void {
            $this->findDwsProvisionReportUseCase
                ->expects('handle')
                ->andReturn(
                    FinderResult::from(
                        [
                            $this->provisionReports[0]->copy([
                                'plans' => [
                                    $this->provisionReports[0]->plans[0]->copy([
                                        'category' => DwsProjectServiceCategory::physicalCare(),
                                    ]),
                                ],
                                'results' => [
                                    $this->provisionReports[0]->results[0]->copy([
                                        'category' => DwsProjectServiceCategory::ownExpense(),
                                    ]),
                                ],
                            ]),
                            $this->provisionReports[1]->copy([
                                'plans' => [
                                    $this->provisionReports[1]->plans[0]->copy([
                                        'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                                    ]),
                                ],
                                'results' => [
                                    $this->provisionReports[1]->results[0]->copy([
                                        'category' => DwsProjectServiceCategory::ownExpense(),
                                    ]),
                                ],
                            ]),
                        ],
                        Pagination::create()
                    )
                );

            $this->assertTrue($customValidator()->fails());
        });
        $this->should('pass when coordination is self although provisionReports do not contain dws result', function () use ($customValidator): void {
            $officeId = 10;
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->billing->copy([
                    'office' => $this->examples->dwsBillings[0]->office->copy([
                        'officeId' => $officeId,
                    ]),
                ])));
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->examples->dwsCertifications[0]->copy([
                    'userId' => $this->statements[0]->user->userId,
                    'copayCoordination' => $this->examples->dwsCertifications[0]->copayCoordination->copy([
                        'officeId' => $officeId,
                    ]),
                ])));
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->andReturn(Option::some($this->examples->dwsCertifications[1]->copy([
                    'userId' => $this->statements[1]->user->userId,
                    'copayCoordination' => $this->examples->dwsCertifications[1]->copayCoordination->copy([
                        'officeId' => $officeId,
                    ]),
                ])));
            $this->findDwsProvisionReportUseCase
                ->expects('handle')
                ->andReturn(
                    FinderResult::from(
                        [
                            $this->provisionReports[0]->copy([
                                'userId' => $this->statements[0]->user->userId,
                                'plans' => [
                                    $this->provisionReports[0]->plans[0]->copy([
                                        'category' => DwsProjectServiceCategory::physicalCare(),
                                    ]),
                                ],
                                'results' => [
                                    $this->provisionReports[0]->results[0]->copy([
                                        'category' => DwsProjectServiceCategory::ownExpense(),
                                    ]),
                                ],
                            ]),
                            $this->provisionReports[1]->copy([
                                'userId' => $this->statements[1]->user->userId,
                                'plans' => [
                                    $this->provisionReports[1]->plans[0]->copy([
                                        'category' => DwsProjectServiceCategory::visitingCareForPwsd(),
                                    ]),
                                ],
                                'results' => [
                                    $this->provisionReports[1]->results[0]->copy([
                                        'category' => DwsProjectServiceCategory::ownExpense(),
                                    ]),
                                ],
                            ]),
                        ],
                        Pagination::create()
                    )
                );

            $this->assertTrue($customValidator()->passes());
        });
    }
}
