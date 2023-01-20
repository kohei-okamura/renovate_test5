<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\LtcsBillingStatement;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\EnsureLtcsBillingBundleUseCaseMixin;
use Tests\Unit\Mixins\LtcsBillingStatementRepositoryMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;
use UseCase\Billing\SimpleLookupLtcsBillingStatementInteractor;

/**
 * {@link \UseCase\Billing\SimpleLookupLtcsBillingStatementInteractor} Test.
 */
class SimpleLookupLtcsBillingStatementInteractorTest extends Test
{
    use ContextMixin;
    use LtcsBillingStatementRepositoryMixin;
    use EnsureLtcsBillingBundleUseCaseMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use UnitSupport;

    private LtcsBillingStatement $ltcsBillingStatement;
    private SimpleLookupLtcsBillingStatementInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (SimpleLookupLtcsBillingStatementInteractorTest $self): void {
            $self->ltcsBillingStatement = $self->examples->ltcsBillingStatements[0];

            $self->ltcsBillingStatementRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->ltcsBillingStatement))
                ->byDefault();
            $self->ensureLtcsBillingBundleUseCase
                ->allows('handle')
                ->andReturnNull()
                ->byDefault();

            $self->interactor = app(SimpleLookupLtcsBillingStatementInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('return a Seq of LtcsBillingStatement', function (): void {
            $actual = $this->interactor
                ->handle(
                    $this->context,
                    Permission::viewBillings(),
                    $this->ltcsBillingStatement->id
                );

            $this->assertCount(1, $actual);
            $this->assertModelStrictEquals(
                $this->ltcsBillingStatement,
                $actual->head()
            );
        });
        $this->should('use EnsureLtcsBillingUseCase', function (): void {
            $this->ensureLtcsBillingBundleUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::viewBillings(),
                    $this->ltcsBillingStatement->billingId,
                    $this->ltcsBillingStatement->bundleId
                )
                ->andReturnNull();

            $this->assertCount(
                1,
                $this->interactor->handle(
                    $this->context,
                    Permission::viewBillings(),
                    $this->ltcsBillingStatement->id
                )
            );
        });
        $this->should('return empty when Repository returns not match BundleId.', function (): void {
            $errorStatement = $this->examples->ltcsBillingStatements[1]->copy([
                'bundleId' => self::NOT_EXISTING_ID,
            ]);
            $this->ltcsBillingStatementRepository
                ->allows('lookup')
                ->andReturn(Seq::from(
                    $this->examples->ltcsBillingStatements[0],
                    $errorStatement,
                ));
            $this->ensureLtcsBillingBundleUseCase
                ->allows('handle')
                ->andThrow(new NotFoundException("LtcsBillingBundle({$errorStatement->bundleId}) not found"));

            $this->assertThrows(
                NotFoundException::class,
                function () {
                    $this->interactor->handle(
                        $this->context,
                        Permission::viewBillings(),
                        $this->examples->ltcsBillingStatements[0]->id,
                        $this->examples->ltcsBillingStatements[1]->id
                    );
                }
            );
        });
    }
}
