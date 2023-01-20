<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingFile;
use Domain\Common\Carbon;
use Domain\Common\MimeType;
use Lib\Exceptions\LogicException;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildDwsBillingInvoicePdfUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GenerateFileNameUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\StorePdfUseCaseMixin;
use Tests\Unit\Mixins\TokenMakerMixin;
use Tests\Unit\Test;
use UseCase\Billing\CreateDwsBillingInvoicePdfInteractor;

/**
 * {@link \UseCase\Billing\CreateDwsBillingInvoicePdfInteractor} のテスト.
 */
final class CreateDwsBillingInvoicePdfInteractorTest extends Test
{
    use BuildDwsBillingInvoicePdfUseCaseMixin;
    use GenerateFileNameUseCaseMixin;
    use CarbonMixin;
    use ConfigMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use StorePdfUseCaseMixin;
    use TokenMakerMixin;
    use UnitSupport;

    private const FILENAME = 'dummy.pdf';

    private DwsBilling $dwsBilling;
    /** @var \Domain\Billing\DwsBillingBundle[]&\ScalikePHP\Seq */
    private Seq $dwsBillingBundles;
    private CreateDwsBillingInvoicePdfInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (CreateDwsBillingInvoicePdfInteractorTest $self): void {
            $self->buildDwsBillingInvoicePdfUseCase
                ->allows('handle')
                ->andReturn([])
                ->byDefault();
            $self->config
                ->allows('get')
                ->with('zinger.filename.dws_invoice_pdf')
                ->andReturn('介護給付費請求書_#{office}_#{transactedIn}.pdf')
                ->byDefault();
            $self->storePdfUseCase
                ->allows('handle')
                ->andReturn('path/to/stored-file.pdf')
                ->byDefault();
            $self->tokenMaker
                ->allows('make')
                ->andReturn(str_repeat('x', 60))
                ->byDefault();

            $self->dwsBilling = $self->examples->dwsBillings[0];
            $self->dwsBillingBundles = Seq::from($self->examples->dwsBillingBundles[0]);
            $self->interactor = app(CreateDwsBillingInvoicePdfInteractor::class);
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
        $this->should('build params using BuildDwsBillingInvoicePdfUseCase', function (): void {
            $params = [];
            $this->buildDwsBillingInvoicePdfUseCase
                ->expects('handle')
                ->with($this->context, $this->dwsBilling, $this->dwsBillingBundles)
                ->andReturn($params);

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
            $template = 'pdfs.billings.dws-billing-invoice.index';
            $this->storePdfUseCase
                ->expects('handle')
                ->with($this->context, 'artifacts', $template, [])
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
