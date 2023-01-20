<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingStatement;
use Domain\Permission\Permission;
use Lib\Exceptions\InvalidArgumentException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EnsureLtcsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\LtcsBillingStatementRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\LookupLtcsBillingStatementInteractor;

/**
 * {@link \UseCase\Billing\LookupLtcsBillingStatementInteractor} のテスト.
 */
final class LookupLtcsBillingStatementInteractorTest extends Test
{
    use ContextMixin;
    use EnsureLtcsBillingBundleUseCaseMixin;
    use ExamplesConsumer;
    use LtcsBillingStatementRepositoryMixin;
    use MockeryMixin;
    use UnitSupport;

    private LtcsBilling $billing;
    private LtcsBillingBundle $bundle;
    private LtcsBillingStatement $statement;

    private LookupLtcsBillingStatementInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->billing = $self->examples->ltcsBillings[0];
            $self->bundle = $self->examples->ltcsBillingBundles[0];
            $self->statement = $self->examples->ltcsBillingStatements[0];
        });
        self::beforeEachSpec(function (self $self): void {
            $self->ltcsBillingStatementRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->statement))
                ->byDefault();

            $self->ensureLtcsBillingBundleUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();

            $self->interactor = app(LookupLtcsBillingStatementInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('accept an integer as the 3rd/4th arguments', function (): void {
            $actual = $this->interactor->handle(
                $this->context,
                Permission::viewBillings(),
                $this->billing->id,
                $this->bundle->id,
                $this->statement->id
            );
            $this->assertInstanceOf(Seq::class, $actual);
        });
        $this->should(
            'accept an instance of LtcsBilling/LtcsBillingBundle as the 3rd/4th arguments',
            function (): void {
                $actual = $this->interactor->handle(
                    $this->context,
                    Permission::viewBillings(),
                    $this->billing,
                    $this->bundle,
                    $this->statement->id
                );
                $this->assertInstanceOf(Seq::class, $actual);
            }
        );
        $this->should(
            'throw an InvalidArgumentException when unexpected arguments given',
            function ($billing, $bundle): void {
                $this->assertThrows(InvalidArgumentException::class, function () use ($billing, $bundle): void {
                    $this->interactor->handle(
                        $this->context,
                        Permission::viewBillings(),
                        $billing,
                        $bundle,
                        $this->statement->id
                    );
                });
            },
            [
                'examples' => [
                    'int, LtcsBillingBundle' => [$this->billing->id, $this->bundle],
                    'LtcsBilling, int' => [$this->billing, $this->bundle->id],
                    'int, string' => [$this->billing->id, (string)$this->bundle->id],
                    'string, int' => [(string)$this->billing->id, $this->bundle->id],
                    'string, LtcsBillingBundle' => [(string)$this->billing->id, $this->bundle],
                    'LtcsBilling, string' => [$this->billing, (string)$this->bundle->id],
                ],
            ]
        );
        $this->should('return a Seq of LtcsBillingStatement', function (): void {
            $actual = $this->interactor->handle(
                $this->context,
                Permission::viewBillings(),
                $this->billing->id,
                $this->bundle->id,
                $this->statement->id
            );

            $this->assertInstanceOf(Seq::class, $actual);
            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals($this->statement, $actual->head());
        });
        $this->should(
            'ensure the bundle is available using EnsureLtcsBillingBundleUseCase when the 3rd/4th arguments are integer',
            function (): void {
                $this->ensureLtcsBillingBundleUseCase
                    ->expects('handle')
                    ->with($this->context, Permission::viewBillings(), $this->billing->id, $this->bundle->id)
                    ->andReturnNull();

                $this->interactor->handle(
                    $this->context,
                    Permission::viewBillings(),
                    $this->billing->id,
                    $this->bundle->id,
                    $this->statement->id
                );
            }
        );
        $this->should(
            'omit ensuring the bundle is available when the 3rd/4th arguments are not integer',
            function (): void {
                $this->ensureLtcsBillingBundleUseCase->expects('handle')->never();

                $this->interactor->handle(
                    $this->context,
                    Permission::viewBillings(),
                    $this->billing,
                    $this->bundle,
                    $this->statement->id
                );
            }
        );
        $this->should('return an empty Seq when the entities do not match bundleId', function (): void {
            $this->ltcsBillingStatementRepository
                ->expects('lookup')
                ->with(1, 2)
                ->andReturn(Seq::from(
                    $this->examples->ltcsBillingStatements[0],
                    $this->examples->ltcsBillingStatements[1]->copy(['bundleId' => self::NOT_EXISTING_ID]),
                ));

            $actual = $this->interactor->handle(
                $this->context,
                Permission::viewBillings(),
                $this->billing->id,
                $this->bundle->id,
                1,
                2
            );

            $this->assertEmpty($actual);
        });
    }
}
