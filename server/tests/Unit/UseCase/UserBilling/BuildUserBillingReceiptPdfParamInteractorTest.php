<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\UserBilling;

use Domain\Common\Carbon;
use Domain\Permission\Permission;
use Domain\UserBilling\UserBilling;
use Domain\UserBilling\UserBillingReceiptPdf;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\UserBilling\BuildUserBillingReceiptPdfParamInteractor;

/**
 * {@link \UseCase\UserBilling\BuildUserBillingReceiptPdfParamInteractor} のテスト.
 */
final class BuildUserBillingReceiptPdfParamInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LookupUserUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    private BuildUserBillingReceiptPdfParamInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->users[0]))
                ->byDefault();
            $self->interactor = app(BuildUserBillingReceiptPdfParamInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return array that contains Seq of UserBillingReceiptPdf with the key billings', function (): void {
            $users = [
                $this->examples->users[0],
                $this->examples->users[1],
            ];
            $userBillings = [
                $this->examples->userBillings[0],
                $this->examples->userBillings[1],
            ];
            foreach ($userBillings as $key => $x) {
                $this->lookupUserUseCase
                    ->allows('handle')
                    ->with($this->context, Permission::viewUserBillings(), $x->userId)
                    ->andReturn(Seq::from($users[$key]));
            }
            $userBillingsSeq = Seq::fromArray($userBillings);
            $issuedOn = Carbon::parse('2021-11-10');
            $actual = $this->interactor->handle($this->context, $userBillingsSeq, $issuedOn);
            $expected = $userBillingsSeq
                ->sortBy(fn (UserBilling $x) => $x->user->name->phoneticDisplayName)
                ->map(fn (UserBilling $x, int $key) => UserBillingReceiptPdf::from(
                    $users[$key],
                    $x,
                    $issuedOn
                ))->toArray();
            $this->assertArrayHasKey('billings', $actual);
            $this->assertArrayStrictEquals(
                $expected,
                $actual['billings']->toArray()
            );
        });
        $this->should('use LookupUserUseCase', function (): void {
            $this->lookupUserUseCase
                ->expects('handle')
                ->andReturn(Seq::from($this->examples->users[0]));
            $this->interactor
                ->handle(
                    $this->context,
                    Seq::from($this->examples->userBillings[0]),
                    Carbon::parse('2021-11-10')
                );
        });
        $this->should('throw NotFoundException if user not found', function (): void {
            $this->lookupUserUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    Seq::from($this->examples->userBillings[0]),
                    Carbon::parse('2021-11-10')
                );
            });
        });
    }
}
