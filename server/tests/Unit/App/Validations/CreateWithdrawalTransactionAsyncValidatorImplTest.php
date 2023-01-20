<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations;

use App\Validations\CreateWithdrawalTransactionAsyncValidatorImpl;
use Domain\Context\Context;
use Domain\Permission\Permission;
use Domain\User\PaymentMethod;
use Lib\Exceptions\ValidationException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LookupUserBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\CreateWithdrawalTransactionAsyncValidatorImpl} のテスト.
 */
final class CreateWithdrawalTransactionAsyncValidatorImplTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use LookupUserBillingUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    private CreateWithdrawalTransactionAsyncValidatorImpl $validator;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from(
                    $self->examples->userBillings[0]->copy([
                        'user' => $self->examples->userBillings[0]->user->copy([
                            'billingDestination' => $self->examples->userBillings[0]->user->billingDestination->copy([
                                'paymentMethod' => PaymentMethod::withdrawal(),
                            ]),
                        ]),
                        'transactedAt' => null,
                    ]),
                    $self->examples->userBillings[1]->copy([
                        'user' => $self->examples->userBillings[1]->user->copy([
                            'billingDestination' => $self->examples->userBillings[1]->user->billingDestination->copy([
                                'paymentMethod' => PaymentMethod::withdrawal(),
                            ]),
                        ]),
                        'transactedAt' => null,
                    ])
                ))
                ->byDefault();
            $self->lookupUserBillingUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::createWithdrawalTransactions(), $self->examples->userBillings[4]->id)
                ->andReturn(Seq::from(
                    $self->examples->userBillings[4]->copy([
                        'user' => $self->examples->userBillings[3]->user->copy([
                            'billingDestination' => $self->examples->userBillings[3]->user->billingDestination->copy([
                                'paymentMethod' => PaymentMethod::withdrawal(),
                            ]),
                        ]),
                        'dwsItem' => $self->examples->userBillings[4]->dwsItem->copy([
                            'copayWithTax' => 0,
                        ]),
                        'ltcsItem' => null,
                        'otherItems' => [],
                        'carriedOverAmount' => 0,
                        'transactedAt' => null,
                    ]),
                ))
                ->byDefault();

            $self->validator = app(CreateWithdrawalTransactionAsyncValidatorImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validation(): void
    {
        $this->should('not throw ValidationException when the data passes the validation rules', function (): void {
            $this->validator->validate($this->context, $this->defaultInput());
        });

        $examples = [
            'when UserBillings with userBillingIds do not contain amount greater than 0' => [
                ['全ての請求金額が0円となるため全銀ファイルを作成できません。'],
                ['userBillingIds' => [$this->examples->userBillings[4]->id]],
                ['userBillingIds' => $this->defaultInput()['userBillingIds']],
            ],
        ];
        $this->should(
            'throw ValidationException when the data does not pass the validation rules',
            function ($expected, $invalid, $valid): void {
                try {
                    $this->validator->validate($this->context, $invalid + $this->defaultInput());
                    assert(false, 'unreachable when throw ValidationException');
                } catch (ValidationException $e) {
                    $this->assertSame($expected, Seq::fromArray($e->getErrors())->toArray());
                }
                if ($valid !== null) {
                    $this->validator->validate($this->context, $valid + $this->defaultInput());
                }
            },
            compact('examples')
        );
    }

    /**
     * リクエストクラスが受け取る入力のデフォルト値.
     *
     * @return array
     */
    private function defaultInput(): array
    {
        return ['userBillingIds' => [$this->examples->userBillings[0]->id, $this->examples->userBillings[1]->id]];
    }
}
