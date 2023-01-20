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
use Domain\Billing\LtcsBillingStatementItem;
use Domain\Permission\Permission;
use Domain\ServiceCode\ServiceCode;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\LookupLtcsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\LookupLtcsBillingStatementUseCaseMixin;
use Tests\Unit\Mixins\LookupLtcsBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\GetLtcsBillingStatementInfoInteractor;
use UseCase\Billing\GetLtcsBillingStatementInfoUseCase;

/**
 * {@link \UseCase\Billing\GetLtcsBillingStatementInfoInteractor} のテスト.
 */
final class GetLtcsBillingStatementInfoInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use LookupLtcsBillingBundleUseCaseMixin;
    use LookupLtcsBillingStatementUseCaseMixin;
    use LookupLtcsBillingUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    private LtcsBilling $billing;
    private LtcsBillingBundle $bundle;
    private LtcsBillingStatement $billingStatement;
    private LtcsBillingStatementItem $defaultBillingStatementItem;

    private GetLtcsBillingStatementInfoUseCase $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (GetLtcsBillingStatementInfoInteractorTest $self): void {
            $self->billing = $self->examples->ltcsBillings[0];
            $self->bundle = $self->examples->ltcsBillingBundles[1];
            $self->defaultBillingStatementItem = $self->examples->ltcsBillingStatements[2]->items[0];
            $self->billingStatement = $self->examples->ltcsBillingStatements[2]->copy([
                'items' => [
                    $self->defaultBillingStatementItem->copy([
                        'serviceCode' => ServiceCode::fromString('110000'), // 居宅のサービスコード
                    ]),
                    $self->defaultBillingStatementItem->copy([
                        'serviceCode' => ServiceCode::fromString('120000'), // 重訪のサービスコード
                    ]),
                ],
            ]);

            $self->lookupLtcsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->billing))
                ->byDefault();
            $self->lookupLtcsBillingBundleUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->bundle))
                ->byDefault();
            $self->lookupLtcsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->billingStatement))
                ->byDefault();

            $self->interactor = app(GetLtcsBillingStatementInfoInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return assoc with parameters', function (): void {
            $actual = $this->interactor->handle(
                $this->context,
                $this->billing->id,
                $this->bundle->id,
                $this->billingStatement->id
            );

            $this->assertArrayHasKey('billing', $actual);
            $this->assertArrayHasKey('bundle', $actual);
            $this->assertArrayHasKey('statement', $actual);

            $this->assertModelStrictEquals($this->billing, $actual['billing']);
            $this->assertModelStrictEquals($this->bundle, $actual['bundle']);
            $this->assertModelStrictEquals($this->billingStatement, $actual['statement']);
        });
        $this->should('use LookupLtcsBillingUseCase', function (): void {
            $this->lookupLtcsBillingUseCase
                ->expects('handle')
                ->with($this->context, Permission::viewBillings(), $this->billing->id)
                ->andReturn(Seq::from($this->billing));

            $this->interactor->handle(
                $this->context,
                $this->billing->id,
                $this->bundle->id,
                $this->billingStatement->id
            );
        });
        $this->should('use LookupLtcsBillingBundleUseCase', function (): void {
            $this->lookupLtcsBillingBundleUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::viewBillings(),
                    $this->billing,
                    $this->bundle->id
                )
                ->andReturn(Seq::from($this->bundle));

            $this->interactor->handle(
                $this->context,
                $this->billing->id,
                $this->bundle->id,
                $this->billingStatement->id
            );
        });
        $this->should('use LookupLtcsBillingStatementUseCase', function (): void {
            $this->lookupLtcsBillingStatementUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::viewBillings(),
                    $this->billing,
                    $this->bundle,
                    $this->billingStatement->id
                )
                ->andReturn(Seq::from($this->billingStatement));

            $this->interactor->handle(
                $this->context,
                $this->billing->id,
                $this->bundle->id,
                $this->billingStatement->id
            );
        });
        $this->should('throw NotFoundException when LookupLtcsBillingUseCase return Empty', function (): void {
            $this->lookupLtcsBillingUseCase
                ->allows('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->billing->id,
                        $this->bundle->id,
                        $this->billingStatement->id
                    );
                }
            );
        });
        $this->should('throw NotFoundException when LookupLtcsBillingBundleUseCase return Empty', function (): void {
            $this->lookupLtcsBillingBundleUseCase
                ->allows('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->billing->id,
                        $this->bundle->id,
                        $this->billingStatement->id
                    );
                }
            );
        });
        $this->should('throw NotFoundException when LookupLtcsBillingStatementUseCase return Empty', function (): void {
            $this->lookupLtcsBillingStatementUseCase
                ->allows('handle')
                ->andReturn(Seq::emptySeq());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->billing->id,
                        $this->bundle->id,
                        $this->billingStatement->id
                    );
                }
            );
        });
    }
}
