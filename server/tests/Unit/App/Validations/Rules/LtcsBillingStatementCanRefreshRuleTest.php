<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use App\Validations\CustomValidator;
use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingStatus;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Project\LtcsProjectServiceCategory;
use Domain\ProvisionReport\LtcsProvisionReportStatus;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\FindLtcsProvisionReportUseCaseMixin;
use Tests\Unit\Mixins\LookupLtcsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\LookupLtcsBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\SimpleLookupLtcsBillingStatementUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\LtcsBillingStatementCanRefreshRule} のテスト.
 */
final class LtcsBillingStatementCanRefreshRuleTest extends Test
{
    use ExamplesConsumer;
    use FindLtcsProvisionReportUseCaseMixin;
    use LookupLtcsBillingBundleUseCaseMixin;
    use LookupLtcsBillingUseCaseMixin;
    use MockeryMixin;
    use RuleTestSupport;
    use SimpleLookupLtcsBillingStatementUseCaseMixin;
    use UnitSupport;

    /** @var \Domain\Billing\LtcsBilling */
    private LtcsBilling $billing;
    /** @var \Domain\Billing\LtcsBillingStatement[] */
    private array $validBillingStatements;
    /** @var \Domain\ProvisionReport\LtcsProvisionReport[] */
    private array $validProvisionReports;
    /** @var \Domain\Common\Pagination */
    private Pagination $pagination;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
        });
        self::beforeEachSpec(function (self $self): void {
            $self->billing = $self->examples->ltcsBillings[0]->copy(['status' => LtcsBillingStatus::checking()]);
            $self->validBillingStatements = [
                $self->examples->ltcsBillingStatements[0]->copy(['status' => LtcsBillingStatus::ready()]),
                $self->examples->ltcsBillingStatements[1]->copy(['status' => LtcsBillingStatus::ready()]),
            ];
            $self->validProvisionReports = [
                $self->examples->ltcsProvisionReports[0]->copy([
                    'status' => LtcsProvisionReportStatus::fixed(),
                    'entries' => [
                        $self->examples->ltcsProvisionReports[0]->entries[0]->copy([
                            'category' => LtcsProjectServiceCategory::physicalCare(),
                            'results' => [Carbon::parse('2020-10-13')],
                        ]),
                    ],
                ]),
                $self->examples->ltcsProvisionReports[1]->copy([
                    'status' => LtcsProvisionReportStatus::fixed(),
                    'entries' => [
                        $self->examples->ltcsProvisionReports[1]->entries[0]->copy([
                            'category' => LtcsProjectServiceCategory::housework(),
                            'results' => [Carbon::parse('2020-10-20')],
                        ]),
                    ],
                ]),
            ];
            $self->pagination = Pagination::create(['all' => true]);

            $bundles = Seq::fromArray($self->examples->ltcsBillingBundles)
                ->filter(fn (LtcsBillingBundle $x) => $x->billingId === $self->billing->id);
            $provisionReports = FinderResult::from($self->validProvisionReports, $self->pagination);

            $self->lookupLtcsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->billing))
                ->byDefault();
            $self->simpleLookupLtcsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(Seq::fromArray($self->validBillingStatements))
                ->byDefault();
            $self->lookupLtcsBillingBundleUseCase
                ->allows('handle')
                ->andReturn($bundles)
                ->byDefault();
            $self->findLtcsProvisionReportUseCase
                ->allows('handle')
                ->andReturn($provisionReports)
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateLtcsBillingStatementCanRefreshRule(): void
    {
        $customValidator = function (array $dataOverwrite = []): CustomValidator {
            return CustomValidator::make(
                $this->context,
                $dataOverwrite + [
                    'billingId' => $this->billing->id,
                    'ids' => [
                        $this->validBillingStatements[0]->id,
                        $this->validBillingStatements[1]->id,
                    ],
                ],
                [
                    'ids' => 'ltcs_billing_statement_can_refresh',
                ],
                [],
                []
            );
        };
        $this->should('pass when billingId is invalid', function () use ($customValidator): void {
            $billingId = 'error';
            $this->assertTrue($customValidator(compact('billingId'))->passes());
        });
        $this->should('pass when ids is not array', function () use ($customValidator): void {
            $ids = 1;
            $this->assertTrue($customValidator(compact('ids'))->passes());
        });
        $this->should('fail when billing is not found', function () use ($customValidator): void {
            $this->lookupLtcsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());

            $this->assertTrue($customValidator()->fails());
        });
        $this->should('fail when billing status is fixed', function () use ($customValidator): void {
            $this->lookupLtcsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->billing->copy(['status' => LtcsBillingStatus::fixed()])));

            $this->assertTrue($customValidator()->fails());
        });
        $this->should('fail if the number of ids is different from the number of statements', function () use ($customValidator): void {
            $this->simpleLookupLtcsBillingStatementUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->validBillingStatements[0]));

            $this->assertTrue($customValidator()->fails());
        });
        $this->should('fail if statements contain a statement that is fixed', function () use ($customValidator): void {
            $this->simpleLookupLtcsBillingStatementUseCase
                ->expects('handle')
                ->andReturn(Seq::from(
                    $this->validBillingStatements[0],
                    $this->validBillingStatements[1]->copy(['status' => LtcsBillingStatus::fixed()])
                ));

            $this->assertTrue($customValidator()->fails());
        });
        $this->should('fail if statements contain multiple bundle id', function () use ($customValidator): void {
            $this->simpleLookupLtcsBillingStatementUseCase
                ->expects('handle')
                ->andReturn(Seq::from(
                    $this->validBillingStatements[0]->copy(['bundleId' => 1]),
                    $this->validBillingStatements[1]->copy(['bundleId' => 2])
                ));

            $this->assertTrue($customValidator()->fails());
        });
        $this->should('fail when bundle is not found', function () use ($customValidator): void {
            $this->lookupLtcsBillingBundleUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());

            $this->assertTrue($customValidator()->fails());
        });
        $this->should('fail if the number of provision reports is different from the number of statements', function () use ($customValidator): void {
            $this->findLtcsProvisionReportUseCase
                ->expects('handle')
                ->andReturn(FinderResult::from(
                    [$this->validProvisionReports[0]],
                    $this->pagination
                ));

            $this->assertTrue($customValidator()->fails());
        });
        $this->should('fail if provisionReports contain a report that is notCreated', function () use ($customValidator): void {
            $this->findLtcsProvisionReportUseCase
                ->expects('handle')
                ->andReturn(FinderResult::from(
                    [
                        $this->validProvisionReports[0],
                        $this->validProvisionReports[1]->copy(['status' => LtcsProvisionReportStatus::notCreated()]),
                    ],
                    $this->pagination
                ));

            $this->assertTrue($customValidator()->fails());
        });
        $this->should('fail if provisionReports contain a report that is inProgress', function () use ($customValidator): void {
            $this->findLtcsProvisionReportUseCase
                ->expects('handle')
                ->andReturn(FinderResult::from(
                    [
                        $this->validProvisionReports[0],
                        $this->validProvisionReports[1]->copy(['status' => LtcsProvisionReportStatus::inProgress()]),
                    ],
                    $this->pagination
                ));

            $this->assertTrue($customValidator()->fails());
        });
        $this->should('fail if provisionReports contain a report that only has ownExpense', function () use ($customValidator): void {
            $this->findLtcsProvisionReportUseCase
                ->expects('handle')
                ->andReturn(FinderResult::from(
                    [
                        $this->validProvisionReports[0],
                        $this->validProvisionReports[1]->copy([
                            'entries' => [
                                $this->validProvisionReports[1]->entries[0]->copy([
                                    'category' => LtcsProjectServiceCategory::ownExpense(),
                                    'results' => [Carbon::parse('2020-10-13')],
                                ]),
                            ],
                        ]),
                    ],
                    $this->pagination
                ));

            $this->assertTrue($customValidator()->fails());
        });
        $this->should('pass when all conditions are correct', function () use ($customValidator): void {
            $this->assertTrue($customValidator()->passes());
        });
    }
}
