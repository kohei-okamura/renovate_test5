<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use App\Validations\CustomValidator;
use Domain\Billing\DwsBillingOffice;
use Domain\Billing\DwsBillingStatementCopayCoordinationStatus;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\LookupDwsBillingUseCaseMixin;
use Tests\Unit\Mixins\SimpleLookupDwsBillingStatementUseCaseMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\CopayListCanDownloadRule} のテスト.
 */
final class CopayListCanDownloadRuleTest extends Test
{
    use ExamplesConsumer;
    use RuleTestSupport;
    use UnitSupport;
    use LookupDwsBillingUseCaseMixin;
    use SimpleLookupDwsBillingStatementUseCaseMixin;

    /** @var \Domain\Billing\DwsBillingStatement[] */
    private array $statements;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
        });
        self::beforeEachSpec(function (self $self): void {
            $self->statements = $self->examples->dwsBillingStatements;
            $self->lookupDwsBillingUseCase
                ->allows('handle')
                ->andReturns(Seq::from($self->examples->dwsBillings[0]))
                ->byDefault();
            $self->simpleLookupDwsBillingStatementUseCase
                ->allows('handle')
                ->andReturns(Seq::from(
                    $self->examples->dwsBillingStatements[0],
                    $self->examples->dwsBillingStatements[1],
                ))
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_copayListCanDownload(): void
    {
        $customValidator = function (array $dataOverwrite = []): CustomValidator {
            return CustomValidator::make(
                $this->context,
                $dataOverwrite + [
                    'billingId' => $this->statements[0]->dwsBillingId,
                    'ids' => [
                        $this->statements[0]->id,
                        $this->statements[1]->id,
                    ],
                ],
                [
                    'ids' => 'copay_list_can_download',
                ]
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
        $this->should('pass when billing is not found', function () use ($customValidator): void {
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());
            $this->assertTrue($customValidator()->passes());
        });
        $this->should('fail when copayCoordinationStatus is not unfilled or fulfilled', function () use ($customValidator): void {
            $this->simpleLookupDwsBillingStatementUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->dwsBillingStatements[0]->copy([
                    'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::checking(),
                ])));
            $this->assertTrue($customValidator()->fails());
        });
        $this->should('fail when copayCoordination officeId is equal billing officeId', function () use ($customValidator): void {
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->dwsBillings[0]->copy([
                    'office' => DwsBillingOffice::from($this->examples->offices[0]),
                ])));
            $this->assertTrue($customValidator()->fails());
        });
        $this->should('pass when copayCoordination is null', function () use ($customValidator): void {
            $this->simpleLookupDwsBillingStatementUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->dwsBillingStatements[0]->copy([
                    'copayCoordination' => null,
                ])));
            $this->assertTrue($customValidator()->passes());
        });
        $this->should('pass when copayCoordinationStatus is unfilled and officeId is not equal billing officeId', function () use ($customValidator): void {
            $this->simpleLookupDwsBillingStatementUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->dwsBillingStatements[0]->copy([
                    'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::unfilled(),
                ])));
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->dwsBillings[0]->copy([
                    'office' => DwsBillingOffice::from($this->examples->offices[1]),
                ])));
            $this->assertTrue($customValidator()->passes());
        });
        $this->should('pass when copayCoordinationStatus is fulfilled and officeId is not equal billing officeId', function () use ($customValidator): void {
            $this->simpleLookupDwsBillingStatementUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->dwsBillingStatements[0]->copy([
                    'copayCoordinationStatus' => DwsBillingStatementCopayCoordinationStatus::fulfilled(),
                ])));
            $this->lookupDwsBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->dwsBillings[0]->copy([
                    'office' => DwsBillingOffice::from($this->examples->offices[1]),
                ])));
            $this->assertTrue($customValidator()->passes());
        });
    }
}
