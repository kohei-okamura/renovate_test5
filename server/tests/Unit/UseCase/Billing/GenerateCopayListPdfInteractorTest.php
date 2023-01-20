<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBilling;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildCopayListPdfParamUseCaseMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\StorePdfUseCaseMixin;
use Tests\Unit\Test;
use UseCase\Billing\GenerateCopayListPdfInteractor;

/**
 * {@link \UseCase\Billing\GenerateCopayListPdfInteractor} のテスト.
 */
final class GenerateCopayListPdfInteractorTest extends Test
{
    use BuildCopayListPdfParamUseCaseMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use StorePdfUseCaseMixin;
    use UnitSupport;

    private DwsBilling $billing;
    private Seq $bundles;
    private Seq $statements;
    private bool $isDivided;
    private GenerateCopayListPdfInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->billing = $self->examples->dwsBillings[0];
            $self->bundles = Seq::from($self->examples->dwsBillingBundles[0]);
            $self->statements = Seq::from($self->examples->dwsBillingStatements[0]);
            $self->isDivided = false;
            $self->buildCopayListPdfParamUseCase
                ->allows('handle')
                ->andReturn([])
                ->byDefault();
            $self->storePdfUseCase
                ->allows('handle')
                ->andReturn('path/to/stored-file.pdf')
                ->byDefault();

            $self->interactor = app(GenerateCopayListPdfInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use BuildCopayListPdfUseCase', function (): void {
            $this->buildCopayListPdfParamUseCase
                ->expects('handle')
                ->with($this->context, $this->billing, $this->bundles, $this->statements)
                ->andReturn([]);

            $this->interactor->handle(
                $this->context,
                $this->billing,
                $this->bundles,
                $this->statements,
            );
        });
        $this->should('use StorePdfUseCase', function (): void {
            $template = 'pdfs.billings.copay-list.index';
            $this->storePdfUseCase
                ->expects('handle')
                ->with($this->context, 'exported', $template, [])
                ->andReturn('path/to/stored-file.pdf');

            $this->interactor->handle(
                $this->context,
                $this->billing,
                $this->bundles,
                $this->statements,
            );
        });
    }
}
