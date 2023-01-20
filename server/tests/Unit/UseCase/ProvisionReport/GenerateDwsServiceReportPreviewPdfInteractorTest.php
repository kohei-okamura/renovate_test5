<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\ProvisionReport;

use Domain\Billing\DwsBillingOffice;
use Domain\Billing\DwsBillingServiceReport;
use Domain\Billing\DwsBillingServiceReportPdf;
use Domain\Billing\DwsBillingStatementContract;
use Domain\Common\Carbon;
use Domain\DwsCertification\DwsCertificationAgreement;
use Domain\Permission\Permission;
use Lib\Exceptions\NotFoundException;
use Mockery;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildDwsBillingServiceReportListByIdUseCaseMixin;
use Tests\Unit\Mixins\BuildDwsBillingStatementContractListUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\IdentifyDwsCertificationUseCaseMixin;
use Tests\Unit\Mixins\IdentifyDwsProvisionReportUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\StorePdfUseCaseMixin;
use Tests\Unit\Test;
use UseCase\ProvisionReport\GenerateDwsServiceReportPreviewPdfInteractor;

/**
 * {@link \UseCase\ProvisionReport\GenerateDwsServiceReportPreviewPdfInteractor} のテスト.
 */
final class GenerateDwsServiceReportPreviewPdfInteractorTest extends Test
{
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;
    use BuildDwsBillingStatementContractListUseCaseMixin;
    use BuildDwsBillingServiceReportListByIdUseCaseMixin;
    use IdentifyDwsProvisionReportUseCaseMixin;
    use IdentifyDwsCertificationUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use LookupUserUseCaseMixin;
    use StorePdfUseCaseMixin;

    private const DUMMY_ID = -1;
    private const STORE_TO = 'exported';
    private const TEMPLATE = 'pdfs.billings.service-report.index';
    private Carbon $providedIn;
    private GenerateDwsServiceReportPreviewPdfInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->providedIn = Carbon::now();
            $self->buildDwsBillingStatementContractListUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsBillingStatements[0]->contracts[0]))
                ->byDefault();
            $self->buildDwsBillingServiceReportListByIdUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->dwsBillingServiceReports[0]))
                ->byDefault();
            $self->identifyDwsProvisionReportUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->dwsProvisionReports[0]))
                ->byDefault();
            $self->identifyDwsProvisionReportUseCase
                ->allows('handle')
                ->with(
                    $self->context,
                    Permission::updateDwsProvisionReports(),
                    $self->examples->offices[0]->id,
                    $self->examples->users[0]->id,
                    equalTo($self->providedIn->subMonth())
                )
                ->andReturn(Option::none())
                ->byDefault();

            $self->identifyDwsCertificationUseCase
                ->allows('handle')
                ->andReturn(Option::from($self->examples->dwsCertifications[0]))
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->users[0]))
                ->byDefault();
            $self->storePdfUseCase
                ->allows('handle')
                ->andReturn('path/to/stored-file.pdf')
                ->byDefault();

            $self->interactor = app(GenerateDwsServiceReportPreviewPdfInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle()
    {
        $this->should('use LookupOfficeUseCase', function (): void {
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    [Permission::updateDwsProvisionReports()],
                    $this->examples->offices[0]->id
                )
                ->andReturn(Seq::from($this->examples->offices[0]));

            $this->interactor->handle($this->context, $this->examples->offices[0]->id, $this->examples->users[0]->id, $this->providedIn);
        });
        $this->should('throw NotFoundException when LookupOfficeUseCase return empty Seq', function (): void {
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    [Permission::updateDwsProvisionReports()],
                    $this->examples->offices[0]->id
                )
                ->andReturn(Seq::empty());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->examples->offices[0]->id,
                        $this->examples->users[0]->id,
                        $this->providedIn
                    );
                }
            );
        });
        $this->should('use GetDwsProvisionReportUseCase to get this month ServiceReport ', function (): void {
            $this->identifyDwsProvisionReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateDwsProvisionReports(),
                    $this->examples->offices[0]->id,
                    $this->examples->users[0]->id,
                    $this->providedIn
                )
                ->andReturn(Option::from($this->examples->dwsProvisionReports[0]));

            $this->interactor->handle($this->context, $this->examples->offices[0]->id, $this->examples->users[0]->id, $this->providedIn);
        });
        $this->should('throw NotFoundException when FindDwsProvisionReport not found', function (): void {
            $this->identifyDwsProvisionReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateDwsProvisionReports(),
                    $this->examples->offices[0]->id,
                    $this->examples->users[0]->id,
                    $this->providedIn
                )
                ->andReturn(Option::none());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->examples->offices[0]->id,
                        $this->examples->users[0]->id,
                        $this->providedIn
                    );
                }
            );
        });
        $this->should('use GetDwsProvisionReportUseCase to get this subMonth ServiceReport ', function (): void {
            $this->identifyDwsProvisionReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateDwsProvisionReports(),
                    $this->examples->offices[0]->id,
                    $this->examples->users[0]->id,
                    equalTo($this->providedIn->subMonth())
                )
                ->andReturn(Option::none());

            $this->interactor->handle($this->context, $this->examples->offices[0]->id, $this->examples->users[0]->id, $this->providedIn);
        });
        $this->should('use LookupUserUseCase', function (): void {
            $this->lookupUserUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateDwsProvisionReports(),
                    $this->examples->users[0]->id
                )
                ->andReturn(Seq::from($this->examples->users[0]));

            $this->interactor->handle($this->context, $this->examples->offices[0]->id, $this->examples->users[0]->id, $this->providedIn);
        });
        $this->should('use IdentifyDwsCertification ', function (): void {
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->examples->users[0]->id,
                    $this->providedIn
                )
                ->andReturn(Option::from($this->examples->dwsCertifications[0]));

            $this->interactor->handle($this->context, $this->examples->offices[0]->id, $this->examples->users[0]->id, $this->providedIn);
        });
        $this->should('throw NotFoundException when IdentifyDwsCertification return empty Seq', function (): void {
            $this->identifyDwsCertificationUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->examples->users[0]->id,
                    $this->providedIn
                )
                ->andReturn(Option::none());

            $this->assertThrows(
                NotFoundException::class,
                function (): void {
                    $this->interactor->handle(
                        $this->context,
                        $this->examples->offices[0]->id,
                        $this->examples->users[0]->id,
                        $this->providedIn
                    );
                }
            );
        });
        $this->should('use BuildDwsBillingStatementContractListUseCase', function (): void {
            $this->buildDwsBillingStatementContractListUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->examples->offices[0],
                    $this->examples->dwsCertifications[0],
                    $this->providedIn
                )
                ->andReturn(Seq::from($this->examples->dwsBillingStatements[0]->contracts[0]));

            $this->interactor->handle($this->context, $this->examples->offices[0]->id, $this->examples->users[0]->id, $this->providedIn);
        });
        $this->should('use storePdfUseCase ', function (): void {
            $contracts = Seq::fromArray($this->examples->dwsCertifications[0]->agreements)
                ->map(function (DwsCertificationAgreement $x) {
                    return DwsBillingStatementContract::from($x);
                });
            $params = Seq::from($this->examples->dwsBillingServiceReports[0])
                ->flatMap(fn (DwsBillingServiceReport $serviceReport): Seq => DwsBillingServiceReportPdf::from(
                    $serviceReport,
                    $this->providedIn,
                    DwsBillingOffice::from($this->examples->offices[0]),
                    $contracts
                ))
                ->toArray();
            $this->storePdfUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    self::STORE_TO,
                    self::TEMPLATE,
                    Mockery::capture($actual)
                )
                ->andReturn('path/to/stored-file.pdf');

            $this->interactor->handle($this->context, $this->examples->offices[0]->id, $this->examples->users[0]->id, $this->providedIn);
            $this->assertEquals($actual, ['pdfs' => $params]);
        });

        $this->should('use BuildDwsBillingServiceReportListByIdUseCase', function (): void {
            $this->buildDwsBillingServiceReportListByIdUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    self::DUMMY_ID,
                    self::DUMMY_ID,
                    $this->examples->dwsProvisionReports[0],
                    Option::none(),
                    $this->examples->users[0],
                    true
                )
                ->andReturn(Seq::from($this->examples->dwsBillingServiceReports[0]));

            $this->interactor->handle($this->context, $this->examples->offices[0]->id, $this->examples->users[0]->id, $this->providedIn);
        });
    }
}
