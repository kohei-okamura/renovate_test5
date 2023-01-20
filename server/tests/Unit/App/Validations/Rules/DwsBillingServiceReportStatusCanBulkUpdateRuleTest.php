<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use App\Validations\CustomValidator;
use Domain\Billing\DwsBillingStatus;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\LookupDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\SimpleLookupDwsBillingServiceReportUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\DwsBillingServiceReportStatusCanBulkUpdateRule} のテスト.
 */
final class DwsBillingServiceReportStatusCanBulkUpdateRuleTest extends Test
{
    use ExamplesConsumer;
    use MockeryMixin;
    use RuleTestSupport;
    use UnitSupport;
    use LookupDwsBillingUseCaseMixin;
    use SimpleLookupDwsBillingServiceReportUseCaseMixin;

    /** @var \Domain\Billing\DwsBillingServiceReport[] */
    private array $serviceReports;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
        });
        self::beforeEachSpec(function (self $self): void {
            $self->serviceReports = $self->examples->dwsBillingServiceReports;
            $self->lookupDwsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsBillings[0]->copy(['status' => DwsBillingStatus::checking()])))
                ->byDefault();
            $self->simpleLookupDwsBillingServiceReportUseCase
                ->allows('handle')
                ->andReturn(Seq::from(
                    $self->serviceReports[0]->copy(['status' => DwsBillingStatus::ready()]),
                    $self->serviceReports[1]->copy(['status' => DwsBillingStatus::ready()]),
                ))
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateDwsBillingServiceReportStatusCanBulkUpdate(): void
    {
        $customValidator = function (array $dataOverwrite = []): CustomValidator {
            return CustomValidator::make(
                $this->context,
                $dataOverwrite + [
                    'billingId' => $this->serviceReports[0]->dwsBillingId,
                    'ids' => [
                        $this->serviceReports[0]->id,
                        $this->serviceReports[1]->id,
                    ],
                    'status' => DwsBillingStatus::ready()->value(),
                ],
                [
                    'ids' => 'dws_billing_service_report_status_can_bulk_update:status',
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
        $this->should('pass when status is invalid', function () use ($customValidator): void {
            $status = self::INVALID_ENUM_VALUE;
            $this->assertTrue($customValidator(compact('status'))->passes());
        });
        $this->should('fail when status is neither ready nor fixed', function () use ($customValidator): void {
            $status = DwsBillingStatus::disabled()->value();
            $this->assertTrue($customValidator(compact('status'))->fails());
        });
        $this->should('fail when billing is not found', function () use ($customValidator): void {
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());

            $this->assertTrue($customValidator()->fails());
        });
        $this->should('fail when billing status is fixed', function () use ($customValidator): void {
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->dwsBillings[0]->copy(['status' => DwsBillingStatus::fixed()])));

            $this->assertTrue($customValidator()->fails());
        });
        $this->should('fail if the number of ids is different from the number of reports', function () use ($customValidator): void {
            $this->simpleLookupDwsBillingServiceReportUseCase
                ->expects('handle')
                ->andReturn(Seq::from(
                    $this->serviceReports[0]->copy(['status' => DwsBillingStatus::ready()])
                ));

            $status = DwsBillingStatus::fixed()->value();
            $this->assertTrue($customValidator(compact('status'))->fails());
        });
        $this->should('fail if status is fixed although target contains the report that is not ready', function () use ($customValidator): void {
            $this->simpleLookupDwsBillingServiceReportUseCase
                ->expects('handle')
                ->andReturn(Seq::from(
                    $this->serviceReports[0]->copy(['status' => DwsBillingStatus::ready()]),
                    $this->serviceReports[1]->copy(['status' => DwsBillingStatus::fixed()]),
                ));

            $status = DwsBillingStatus::fixed()->value();
            $this->assertTrue($customValidator(compact('status'))->fails());
        });
        $this->should('fail if status is ready although target contains the report that is not fixed', function () use ($customValidator): void {
            $this->simpleLookupDwsBillingServiceReportUseCase
                ->expects('handle')
                ->andReturn(Seq::from(
                    $this->serviceReports[0]->copy(['status' => DwsBillingStatus::fixed()]),
                    $this->serviceReports[1]->copy(['status' => DwsBillingStatus::ready()]),
                ));

            $status = DwsBillingStatus::ready()->value();
            $this->assertTrue($customValidator(compact('status'))->fails());
        });
        $this->should('pass when status is fixed and current status of all reports is ready', function () use ($customValidator): void {
            $this->simpleLookupDwsBillingServiceReportUseCase
                ->expects('handle')
                ->andReturn(Seq::from(
                    $this->serviceReports[0]->copy(['status' => DwsBillingStatus::ready()]),
                    $this->serviceReports[1]->copy(['status' => DwsBillingStatus::ready()]),
                ));

            $status = DwsBillingStatus::fixed()->value();
            $this->assertTrue($customValidator(compact('status'))->passes());
        });
        $this->should('pass when status is ready and current status of all reports is fixed', function () use ($customValidator): void {
            $this->simpleLookupDwsBillingServiceReportUseCase
                ->expects('handle')
                ->andReturn(Seq::from(
                    $this->serviceReports[0]->copy(['status' => DwsBillingStatus::fixed()]),
                    $this->serviceReports[1]->copy(['status' => DwsBillingStatus::fixed()]),
                ));

            $status = DwsBillingStatus::ready()->value();
            $this->assertTrue($customValidator(compact('status'))->passes());
        });
    }
}
