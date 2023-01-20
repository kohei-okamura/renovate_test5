<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsBilling;
use Domain\Billing\DwsBillingBundle;
use Domain\Billing\DwsBillingCopayCoordination;
use Domain\Billing\DwsBillingCopayCoordinationPdf;
use Domain\Billing\DwsBillingFile;
use Domain\Common\Carbon;
use Domain\Common\MimeType;
use Lib\Exceptions\LogicException;
use ScalikePHP\None;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildDwsBillingCopayCoordinationPdfUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GenerateFileNameUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\StorePdfUseCaseMixin;
use Tests\Unit\Mixins\TokenMakerMixin;
use Tests\Unit\Test;
use UseCase\Billing\CreateDwsBillingCopayCoordinationPdfInteractor;

/**
 * {@link \UseCase\Billing\CreateDwsBillingCopayCoordinationPdfInteractor} のテスト.
 */
final class CreateDwsBillingCopayCoordinationPdfInteractorTest extends Test
{
    use BuildDwsBillingCopayCoordinationPdfUseCaseMixin;
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

    private array $param;
    private DwsBilling $dwsBilling;
    /** @var \Domain\Billing\DwsBillingBundle[]&\ScalikePHP\Seq */
    private Seq $dwsBillingBundles;
    private CreateDwsBillingCopayCoordinationPdfInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (CreateDwsBillingCopayCoordinationPdfInteractorTest $self): void {
            $bundles = Seq::from($self->examples->dwsBillingBundles[0])->map(fn (DwsBillingBundle $bundle): array => [
                'copayCoordinations' => Seq::from($self->examples->dwsBillingCopayCoordinations[0])->map(
                    fn (
                        DwsBillingCopayCoordination $copayCoordination
                    ): DwsBillingCopayCoordinationPdf => DwsBillingCopayCoordinationPdf::from(
                        $bundle,
                        $copayCoordination
                    )
                ),
            ]);
            $self->param = [
                'bundles' => $bundles,
            ];
            $self->buildDwsBillingCopayCoordinationPdfUseCase
                ->allows('handle')
                ->andReturn($self->param)
                ->byDefault();
            $self->config
                ->allows('get')
                ->with('zinger.filename.dws_copay_coordination_pdf')
                ->andReturn('利用者負担上限額管理結果票_#{office}_#{transactedIn}.pdf')
                ->byDefault();
            $self->storePdfUseCase
                ->allows('handle')
                ->andReturn('path/to/stored-file.pdf')
                ->byDefault();
            $self->tokenMaker
                ->allows('make')
                ->andReturn(str_repeat('x', 60))
                ->byDefault();
            $self->generateFileNameUseCase
                ->allows('handle')
                ->andReturn(self::FILENAME)
                ->byDefault();

            $self->dwsBilling = $self->examples->dwsBillings[0];
            $self->dwsBillingBundles = Seq::from($self->examples->dwsBillingBundles[0]);
            $self->interactor = app(CreateDwsBillingCopayCoordinationPdfInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('build params using BuildDwsBillingCopayCoordinationPdfUseCase', function (): void {
            $params = [];
            $this->buildDwsBillingCopayCoordinationPdfUseCase
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
            $template = 'pdfs.billings.dws-billing-copay-coordination.index';
            $this->storePdfUseCase
                ->expects('handle')
                ->with($this->context, 'artifacts', $template, $this->param)
                ->andReturn('path/to/stored-file.pdf');

            $this->interactor->handle($this->context, $this->dwsBilling, $this->dwsBillingBundles);
        });
        $this->should('return DwsBillingFile', function (): void {
            $expected = new DwsBillingFile(
                name: self::FILENAME,
                path: 'path/to/stored-file.pdf',
                token: str_repeat('x', 60),
                mimeType: MimeType::pdf(),
                createdAt: Carbon::now(),
                downloadedAt: null,
            );

            $actual = $this->interactor->handle($this->context, $this->dwsBilling, $this->dwsBillingBundles);
            $this->assertSome($actual, function (DwsBillingFile $actualValue) use ($expected): void {
                $this->assertModelStrictEquals(
                    $expected,
                    $actualValue
                );
            });
        });
        $this->should('return none when copayCoordination params are empty', function (): void {
            $this->buildDwsBillingCopayCoordinationPdfUseCase
                ->expects('handle')
                ->andReturn([]);

            $actual = $this->interactor->handle($this->context, $this->dwsBilling, $this->dwsBillingBundles);
            $this->assertInstanceOf(None::class, $actual);
        });
    }
}
