<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingBundle;
use Domain\Billing\LtcsBillingFile;
use Domain\Common\Carbon;
use Domain\Common\MimeType;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildLtcsBillingInvoicePdfUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GenerateFileNameUseCaseMixin;
use Tests\Unit\Mixins\LookupLtcsBillingUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\StorePdfUseCaseMixin;
use Tests\Unit\Mixins\TokenMakerMixin;
use Tests\Unit\Test;
use UseCase\Billing\CreateLtcsBillingInvoicePdfInteractor;

/**
 * {@link \UseCase\Billing\CreateLtcsBillingInvoicePdfInteractor} のテスト.
 */
final class CreateLtcsBillingInvoicePdfInteractorTest extends Test
{
    use BuildLtcsBillingInvoicePdfUseCaseMixin;
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use GenerateFileNameUseCaseMixin;
    use LookupLtcsBillingUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use StorePdfUseCaseMixin;
    use TokenMakerMixin;
    use UnitSupport;

    private const FILENAME = 'dummy.pdf';

    private LtcsBilling $ltcsBilling;
    private LtcsBillingBundle $ltcsBillingBundle;
    private CreateLtcsBillingInvoicePdfInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (CreateLtcsBillingInvoicePdfInteractorTest $self): void {
            $self->buildLtcsBillingInvoicePdfUseCase
                ->allows('handle')
                ->andReturn([])
                ->byDefault();
            $self->generateFileNameUseCase
                ->allows('handle')
                ->andReturn(self::FILENAME)
                ->byDefault();
            $self->storePdfUseCase
                ->allows('handle')
                ->andReturn('path/to/stored-file.pdf')
                ->byDefault();
            $self->tokenMaker
                ->allows('make')
                ->andReturn(str_repeat('x', 60))
                ->byDefault();

            $self->ltcsBilling = $self->examples->ltcsBillings[0];
            $self->ltcsBillingBundle = $self->examples->ltcsBillingBundles[0];
            $self->interactor = app(CreateLtcsBillingInvoicePdfInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('build params using BuildLtcsBillingInvoicePdfUseCase', function (): void {
            $params = [];
            $this->buildLtcsBillingInvoicePdfUseCase
                ->expects('handle')
                ->with($this->context, $this->ltcsBilling, $this->ltcsBillingBundle)
                ->andReturn($params);

            $this->interactor->handle($this->context, $this->ltcsBilling, $this->ltcsBillingBundle);
        });
        $this->should('generate file name using GenerateFileNameUseCase', function (): void {
            $placeholders = [
                'office' => $this->ltcsBilling->office->abbr,
                'transactedIn' => $this->ltcsBilling->transactedIn,
                'providedIn' => $this->ltcsBillingBundle->providedIn,
            ];
            $this->generateFileNameUseCase
                ->expects('handle')
                ->with('ltcs_invoice_pdf', $placeholders)
                ->andReturn(self::FILENAME);

            $this->interactor->handle($this->context, $this->ltcsBilling, $this->ltcsBillingBundle);
        });
        $this->should('store the pdf using StorePdfUseCase', function (): void {
            $template = 'pdfs.billings.ltcs-billing-invoice.index';
            $this->storePdfUseCase
                ->expects('handle')
                ->with($this->context, 'artifacts', $template, [])
                ->andReturn('path/to/stored-file.pdf');

            $this->interactor->handle($this->context, $this->ltcsBilling, $this->ltcsBillingBundle);
        });
        $this->should('return an instance of LtcsBillingFile', function (): void {
            $expected = new LtcsBillingFile(
                name: self::FILENAME,
                path: 'path/to/stored-file.pdf',
                token: str_repeat('x', 60),
                mimeType: MimeType::pdf(),
                createdAt: Carbon::now(),
                downloadedAt: null,
            );

            $this->assertModelStrictEquals(
                $expected,
                $this->interactor->handle($this->context, $this->ltcsBilling, $this->ltcsBillingBundle)
            );
        });
    }
}
