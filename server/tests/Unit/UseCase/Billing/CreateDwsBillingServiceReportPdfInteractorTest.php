<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingFile;
use Domain\Billing\DwsBillingOffice;
use Domain\Billing\DwsBillingServiceReportPdf;
use Domain\Common\Carbon;
use Domain\Common\MimeType;
use Lib\Exceptions\LogicException;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildDwsHomeHelpServiceServiceReportPdfParamUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GenerateFileNameUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\StorePdfUseCaseMixin;
use Tests\Unit\Mixins\TokenMakerMixin;
use Tests\Unit\Test;
use UseCase\Billing\CreateDwsBillingServiceReportPdfInteractor;

/**
 * {@link \UseCase\Billing\CreateDwsBillingServiceReportPdfInteractor} のテスト.
 */
final class CreateDwsBillingServiceReportPdfInteractorTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ContextMixin;
    use BuildDwsHomeHelpServiceServiceReportPdfParamUseCaseMixin;
    use GenerateFileNameUseCaseMixin;
    use StorePdfUseCaseMixin;
    use ExamplesConsumer;
    use TokenMakerMixin;
    use MockeryMixin;
    use UnitSupport;

    private const FILENAME = 'dummy.pdf';

    private DwsBilling $dwsBilling;
    /** @var \Domain\Billing\DwsBillingBundle[]&\ScalikePHP\Seq */
    private Seq $dwsBillingBundles;
    /** @var \Domain\Billing\DwsBillingServiceReportPdf[]&\ScalikePHP\Seq */
    private Seq $pdf;
    private CreateDwsBillingServiceReportPdfInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->dwsBilling = $self->examples->dwsBillings[0];
            $self->dwsBillingBundles = Seq::from($self->examples->dwsBillingBundles[0]);
            $self->pdf = Seq::from(DwsBillingServiceReportPdf::from(
                $self->examples->dwsBillingServiceReports[0],
                Carbon::now(),
                DwsBillingOffice::from($self->examples->offices[0]),
                Seq::from($self->examples->dwsBillingStatements[0]->contracts[0])
            ));

            $self->buildDwsHomeHelpServiceServiceReportPdfParamUseCase
                ->allows('handle')
                ->andReturn($self->pdf)
                ->byDefault();
            $self->config
                ->allows('get')
                ->with('zinger.filename.dws_service_report_pdf')
                ->andReturn('サービス提供実績記録票_#{office}_#{transactedIn}.pdf')
                ->byDefault();
            $self->storePdfUseCase
                ->allows('handle')
                ->andReturn('path/to/stored-file.pdf')
                ->byDefault();
            $self->tokenMaker
                ->allows('make')
                ->andReturn(str_repeat('x', 60))
                ->byDefault();

            $self->interactor = app(CreateDwsBillingServiceReportPdfInteractor::class);
            $self->generateFileNameUseCase
                ->allows('handle')
                ->andReturn(self::FILENAME)
                ->byDefault();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('build params using BuildDwsHomeHelpServiceServiceReportPdfParamUseCase', function (): void {
            $this->buildDwsHomeHelpServiceServiceReportPdfParamUseCase
                ->expects('handle')
                ->with($this->context, $this->dwsBilling, $this->dwsBillingBundles)
                ->andReturn($this->pdf);

            $this->interactor->handle($this->context, $this->dwsBilling, $this->dwsBillingBundles);
        });
        $this->should('throw LogicException if bundles have multiple providedIn', function (): void {
            $multipleProvideInDwsBillingBundles = Seq::from(
                $this->examples->dwsBillingBundles[0],
                $this->examples->dwsBillingBundles[1]->copy([
                    'providedIn' => Carbon::parse('2022-11-11'),
                ]),
            );
            $this->assertThrows(
                LogicException::class,
                function () use ($multipleProvideInDwsBillingBundles): void {
                    $this->interactor->handle($this->context, $this->dwsBilling, $multipleProvideInDwsBillingBundles);
                }
            );
        });
        $this->should('store the pdf using StorePdfUseCase', function (): void {
            $template = 'pdfs.billings.service-report.index';
            $this->storePdfUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    'artifacts',
                    $template,
                    ['pdfs' => $this->pdf->toArray()]
                )
                ->andReturn('path/to/stored-file.pdf');

            $this->interactor->handle($this->context, $this->dwsBilling, $this->dwsBillingBundles);
        });
        $this->should('return an instance of DwsBillingFile', function (): void {
            $expected = new DwsBillingFile(
                name: self::FILENAME,
                path: 'path/to/stored-file.pdf',
                token: str_repeat('x', 60),
                mimeType: MimeType::pdf(),
                createdAt: Carbon::now(),
                downloadedAt: null,
            );

            $this->assertModelStrictEquals(
                $expected,
                $this->interactor->handle($this->context, $this->dwsBilling, $this->dwsBillingBundles)
            );
        });
    }
}
