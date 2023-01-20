<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBillingStatement;
use Domain\Permission\Permission;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\DwsBillingStatementRepositoryMixin;
use Tests\Unit\Mixins\EnsureDwsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\LookupDwsBillingStatementInteractor;

/**
 * {@link \UseCase\Billing\LookupDwsBillingStatementInteractor} Test.
 */
class LookupDwsBillingStatementInteractorTest extends Test
{
    use ContextMixin;
    use DwsBillingStatementRepositoryMixin;
    use EnsureDwsBillingBundleUseCaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private DwsBillingStatement $dwsBillingStatement;
    private LookupDwsBillingStatementInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LookupDwsBillingStatementInteractorTest $self): void {
            $self->dwsBillingStatement = $self->examples->dwsBillingStatements[0];

            $self->dwsBillingStatementRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->dwsBillingStatement))
                ->byDefault();
            $self->ensureDwsBillingBundleUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();

            $self->interactor = app(LookupDwsBillingStatementInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a Seq of DwsBillingStatement', function (): void {
            $actual = $this->interactor
                ->handle(
                    $this->context,
                    Permission::viewBillings(),
                    $this->dwsBillingStatement->dwsBillingId,
                    $this->dwsBillingStatement->dwsBillingBundleId,
                    $this->dwsBillingStatement->id
                );

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $this->dwsBillingStatement,
                $actual->head()
            );
        });
        $this->should('use EnsureDwsBillingUseCase', function (): void {
            $this->ensureDwsBillingBundleUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::viewBillings(),
                    $this->dwsBillingStatement->dwsBillingId,
                    $this->dwsBillingStatement->dwsBillingBundleId
                )
                ->andReturnNull();

            $this->assertCount(
                1,
                $this->interactor->handle(
                    $this->context,
                    Permission::viewBillings(),
                    $this->dwsBillingStatement->dwsBillingId,
                    $this->dwsBillingStatement->dwsBillingBundleId,
                    $this->dwsBillingStatement->id
                )
            );
        });
        $this->should('return empty when Repository returns not match BundleId.', function (): void {
            $this->dwsBillingStatementRepository
                ->allows('lookup')
                ->andReturn(Seq::from(
                    $this->examples->dwsBillingStatements[0],
                    $this->examples->dwsBillingStatements[1]->copy(['dwsBillingBundleId' => self::NOT_EXISTING_ID]),
                ));

            $this->assertEmpty(
                $this->interactor->handle(
                    $this->context,
                    Permission::viewBillings(),
                    $this->dwsBillingStatement->dwsBillingId,
                    $this->dwsBillingStatement->dwsBillingBundleId,
                    1,
                    2
                )
            );
        });
    }
}
