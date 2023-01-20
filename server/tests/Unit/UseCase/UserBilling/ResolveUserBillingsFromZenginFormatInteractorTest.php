<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\UserBilling;

use Domain\BankAccount\BankAccountType;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Domain\UserBilling\UserBillingResult;
use Domain\UserBilling\WithdrawalResultCode;
use Domain\UserBilling\ZenginDataRecord;
use Domain\UserBilling\ZenginDataRecordCode;
use Domain\UserBilling\ZenginHeaderRecord;
use Domain\UserBilling\ZenginRecord;
use Domain\UserBilling\ZenginTrailerRecord;
use Lib\Exceptions\InvalidArgumentException;
use Lib\Exceptions\ValidationException;
use Mockery;
use ScalikePHP\Map;
use ScalikePHP\Seq;
use SplFileInfo;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\FindWithdrawalTransactionUseCaseMixin;
use Tests\Unit\Mixins\LookupUserBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\ParseZenginFormatUseCaseMixin;
use Tests\Unit\Test;
use UseCase\UserBilling\ResolveUserBillingsFromZenginFormatInteractor;

/**
 * {@link \UseCase\UserBilling\ResolveUserBillingsFromZenginFormatInteractor} のテスト.
 */
final class ResolveUserBillingsFromZenginFormatInteractorTest extends Test
{
    use ContextMixin;
    use CarbonMixin;
    use ExamplesConsumer;
    use FindWithdrawalTransactionUseCaseMixin;
    use LookupUserBillingUseCaseMixin;
    use MockeryMixin;
    use ParseZenginFormatUseCaseMixin;
    use UnitSupport;

    private SplFileInfo $splFileInfo;
    private ZenginRecord $zenginRecord;
    private ResolveUserBillingsFromZenginFormatInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->splFileInfo = new SplFileInfo('dummy');
            $self->zenginRecord = ZenginRecord::create([
                'header' => ZenginHeaderRecord::create([
                    'bankingClientCode' => '0123456789',
                    'bankingClientName' => 'ﾕｰｽﾀｲﾙﾗﾎﾞﾗﾄﾘｰ(ｶ',
                    'deductedOn' => Carbon::now()->subMonths(3),
                ]),
                'data' => [
                    ZenginDataRecord::create([
                        'bankCode' => '0005',
                        'bankBranchCode' => '798',
                        'bankAccountType' => BankAccountType::ordinaryDeposit(),
                        'bankAccountNumber' => '1234567',
                        'bankAccountHolder' => 'ﾀﾅｶ ﾀﾛｳ',
                        'amount' => 28600,
                        'dataRecordCode' => ZenginDataRecordCode::firstTime(),
                        'clientNumber' => '01234567890000000101',
                        'withdrawalResultCode' => WithdrawalResultCode::done(),
                    ]),
                ],
                'trailer' => ZenginTrailerRecord::create([
                    'totalCount' => 1,
                    'totalAmount' => 28600,
                    'succeededCount' => 1,
                    'succeededAmount' => 28600,
                    'failedCount' => 0,
                    'failedAmount' => 0,
                ]),
            ]);
            $self->findWithdrawalTransactionUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from($self->examples->withdrawalTransactions, Pagination::create()))
                ->byDefault();
            $self->parseZenginFormatUseCase
                ->allows('handle')
                ->andReturn($self->zenginRecord)
                ->byDefault();
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->userBillings[0], $self->examples->userBillings[1]))
                ->byDefault();

            $self->interactor = app(ResolveUserBillingsFromZenginFormatInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use ParseZenginFormatUseCase', function (): void {
            $this->parseZenginFormatUseCase
                ->expects('handle')
                ->with($this->context, $this->splFileInfo)
                ->andReturn($this->zenginRecord);

            $this->interactor->handle($this->context, $this->splFileInfo);
        });
        $this->should('throw ValidationException when ParseZenginFormatUseCase throw InvalidArgumentException', function (): void {
            $this->parseZenginFormatUseCase
                ->expects('handle')
                ->andThrow(InvalidArgumentException::class);

            $this->assertThrows(ValidationException::class, function (): void {
                $this->interactor->handle($this->context, $this->splFileInfo);
            });
        });
        $this->should('throw ValidationException when WithdrawalTransactionItems such that clientNumber and amount are equal are not found', function (): void {
            $this->parseZenginFormatUseCase
                ->expects('handle')
                ->andReturn($this->zenginRecord->copy([
                    'data' => [$this->zenginRecord->data[0]->copy([
                        'clientNumber' => '88888888880000000101',
                    ])],
                ]));
            $this->findWithdrawalTransactionUseCase
                ->expects('handle')
                ->andReturn(FinderResult::from($this->examples->withdrawalTransactions, Pagination::create()));

            $this->assertThrows(ValidationException::class, function (): void {
                $this->interactor->handle($this->context, $this->splFileInfo);
            });
        });
        $this->should('throw ValidationException when the deductedOn of the parsed ZenginRecord is over 6 months ago', function (): void {
            $this->parseZenginFormatUseCase
                ->expects('handle')
                ->andReturn($this->zenginRecord->copy([
                    'header' => $this->zenginRecord->header->copy([
                        'deductedOn' => Carbon::today()->subMonths(7),
                    ]),
                ]));

            $this->assertThrows(ValidationException::class, function (): void {
                $this->interactor->handle($this->context, $this->splFileInfo);
            });
        });
        $this->should('throw ValidationException when the withdrawal result of the parsed ZenginRecord is invalid', function (): void {
            $this->parseZenginFormatUseCase
                ->expects('handle')
                ->andReturn($this->zenginRecord->copy([
                    'data' => [
                        ZenginDataRecord::create([
                            'bankCode' => '0005',
                            'bankBranchCode' => '798',
                            'bankAccountType' => BankAccountType::ordinaryDeposit(),
                            'bankAccountNumber' => '1234567',
                            'bankAccountHolder' => 'ﾀﾅｶ ﾀﾛｳ',
                            'amount' => 28600,
                            'dataRecordCode' => ZenginDataRecordCode::firstTime(),
                            'clientNumber' => '01234567890000000101',
                            'withdrawalResultCode' => WithdrawalResultCode::done(),
                        ]),
                        ZenginDataRecord::create([
                            'bankCode' => '0005',
                            'bankBranchCode' => '798',
                            'bankAccountType' => BankAccountType::ordinaryDeposit(),
                            'bankAccountNumber' => '1234567',
                            'bankAccountHolder' => 'ﾀﾅｶ ﾀﾛｳ',
                            'amount' => 28600,
                            'dataRecordCode' => ZenginDataRecordCode::firstTime(),
                            'clientNumber' => '01234567890000000101',
                            'withdrawalResultCode' => WithdrawalResultCode::shortage(),
                        ]),
                    ],
                    'trailer' => $this->zenginRecord->trailer->copy([
                        'succeededCount' => 2,
                    ]),
                ]));

            $this->assertThrows(ValidationException::class, function (): void {
                $this->interactor->handle($this->context, $this->splFileInfo);
            });
        });
        $this->should('use FindWithdrawalTransactionUseCase', function (): void {
            $this->findWithdrawalTransactionUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::downloadWithdrawalTransactions(),
                    ['deductedOn' => $this->zenginRecord->header->deductedOn],
                    ['all' => true]
                )
                ->andReturn(FinderResult::from($this->examples->withdrawalTransactions, Pagination::create()));

            $this->interactor->handle($this->context, $this->splFileInfo);
        });
        $this->should('use LookupUserBillingUseCase once when overlapped WithdrawalTransactionItems do not exist', function (): void {
            $this->findWithdrawalTransactionUseCase
                ->expects('handle')
                ->andReturn(FinderResult::from([$this->examples->withdrawalTransactions[4]], Pagination::create()));
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::downloadWithdrawalTransactions(),
                    ...$this->examples->withdrawalTransactions[4]->items[2]->userBillingIds
                )
                ->andReturn(Seq::from($this->examples->userBillings[0]));

            $this->interactor->handle($this->context, $this->splFileInfo);
        });
        $this->should('use LookupUserBillingUseCase twice or more when overlapped WithdrawalTransactionItems exist', function (): void {
            $this->findWithdrawalTransactionUseCase
                ->expects('handle')
                ->andReturn(FinderResult::from([$this->examples->withdrawalTransactions[5]], Pagination::create()));
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::downloadWithdrawalTransactions(),
                    Mockery::any()
                )
                ->andReturn(Seq::from(
                    $this->examples->userBillings[17],
                    $this->examples->userBillings[18],
                ))
                ->byDefault();
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::downloadWithdrawalTransactions(),
                    ...$this->examples->withdrawalTransactions[5]->items[1]->userBillingIds
                )
                ->andReturn(Seq::from(
                    $this->examples->userBillings[17],
                    $this->examples->userBillings[18],
                ));

            $this->interactor->handle($this->context, $this->splFileInfo);
        });
        $this->should('throw ValidationException when lookupUserBillingUseCase return empty although overlapped WithdrawalTransactionItems exist', function (): void {
            $this->findWithdrawalTransactionUseCase
                ->expects('handle')
                ->andReturn(FinderResult::from([$this->examples->withdrawalTransactions[5]], Pagination::create()));
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());

            $this->assertThrows(ValidationException::class, function () {
                $this->interactor->handle($this->context, $this->splFileInfo);
            });
        });
        $this->should('throw ValidationException when target userBillings contain not in progress', function (): void {
            $this->findWithdrawalTransactionUseCase
                ->expects('handle')
                ->andReturn(FinderResult::from([$this->examples->withdrawalTransactions[5]], Pagination::create()));
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::downloadWithdrawalTransactions(),
                    Mockery::any()
                )
                ->andReturn(Seq::from(
                    $this->examples->userBillings[17],
                    $this->examples->userBillings[18],
                ))
                ->byDefault();
            $this->lookupUserBillingUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::downloadWithdrawalTransactions(),
                    ...$this->examples->withdrawalTransactions[5]->items[1]->userBillingIds
                )
                ->andReturn(Seq::from($this->examples->userBillings[0]->copy([
                    'result' => UserBillingResult::paid(),
                ])));

            $this->assertThrows(ValidationException::class, function () {
                $this->interactor->handle($this->context, $this->splFileInfo);
            });
        });
        $this->should('return Map of userBillingId => [withdrawalResultCode, deductedOn]', function (): void {
            $expected = Map::from([
                '20' => [WithdrawalResultCode::done(), $this->zenginRecord->header->deductedOn],
                '21' => [WithdrawalResultCode::done(), $this->zenginRecord->header->deductedOn],
                '22' => [WithdrawalResultCode::done(), $this->zenginRecord->header->deductedOn],
            ]);
            $actual = $this->interactor->handle($this->context, $this->splFileInfo);
            $this->assertSame(
                $expected->toAssoc(),
                $actual->toAssoc()
            );
        });
    }
}
