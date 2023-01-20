<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\BankAccount;

use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BankAccountRepositoryMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\BankAccount\LookupBankAccountInteractor;

/**
 * LookupBankAccountInteractor のテスト.
 */
final class LookupBankAccountInteractorTest extends Test
{
    use BankAccountRepositoryMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private LookupBankAccountInteractor $interactor;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupBankAccountInteractorTest $self): void {
            $self->interactor = app(LookupBankAccountInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a some of BankAccount', function (): void {
            $this->bankAccountRepository
                ->expects('lookup')
                ->with($this->examples->bankAccounts[0]->id)
                ->andReturn(Seq::from($this->examples->bankAccounts[0]));
            $actual = $this->interactor->handle($this->context, $this->examples->bankAccounts[0]->id);
            $this->assertInstanceOf(Seq::class, $actual);
            $this->assertSame(1, $actual->size());
            $this->assertModelStrictEquals($this->examples->bankAccounts[0], $actual->head());
        });
    }
}
