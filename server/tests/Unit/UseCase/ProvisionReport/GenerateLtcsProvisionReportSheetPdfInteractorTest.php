<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\ProvisionReport;

use Domain\Billing\LtcsBillingServiceDetail;
use Domain\Common\Carbon;
use Domain\Common\Sex;
use Domain\Common\TimeRange;
use Domain\LtcsInsCard\LtcsInsCardStatus;
use Domain\LtcsInsCard\LtcsLevel;
use Domain\Permission\Permission;
use Domain\ProvisionReport\LtcsProvisionReportSheetAppendixPdf;
use Domain\ProvisionReport\LtcsProvisionReportSheetAppendixPdfEntry;
use Domain\ProvisionReport\LtcsProvisionReportSheetPdf;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry;
use Lib\Exceptions\NotFoundException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\BuildLtcsProvisionReportSheetAppendixPdfParamUseCaseMixin;
use Tests\Unit\Mixins\BuildLtcsProvisionReportSheetPdfParamUseCaseMixin;
use Tests\Unit\Mixins\BuildLtcsServiceDetailListUseCaseMixin;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\GetLtcsProvisionReportUseCaseMixin;
use Tests\Unit\Mixins\IdentifyLtcsInsCardUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\LookupUserUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\ResolveLtcsNameFromServiceCodesUseCaseMixin;
use Tests\Unit\Mixins\StorePdfUseCaseMixin;
use Tests\Unit\Test;
use UseCase\ProvisionReport\GenerateLtcsProvisionReportSheetPdfInteractor;

/**
 * {@link \UseCase\ProvisionReport\GenerateLtcsProvisionReportSheetPdfInteractor} のテスト.
 */
final class GenerateLtcsProvisionReportSheetPdfInteractorTest extends Test
{
    use BuildLtcsProvisionReportSheetAppendixPdfParamUseCaseMixin;
    use BuildLtcsProvisionReportSheetPdfParamUseCaseMixin;
    use BuildLtcsServiceDetailListUseCaseMixin;
    use CarbonMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use GetLtcsProvisionReportUseCaseMixin;
    use IdentifyLtcsInsCardUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use LookupUserUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use ResolveLtcsNameFromServiceCodesUseCaseMixin;
    use StorePdfUseCaseMixin;
    use UnitSupport;

    private int $officeId;
    private int $userId;
    private Carbon $providedIn;
    private Carbon $issuedOn;
    private bool $needsMaskingInsNumber;
    private bool $needsMaskingInsName;
    private GenerateLtcsProvisionReportSheetPdfInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->officeId = $self->examples->ltcsProvisionReports[0]->officeId;
            $self->userId = $self->examples->ltcsProvisionReports[0]->userId;
            $self->providedIn = $self->examples->ltcsProvisionReports[0]->providedIn;
            $self->issuedOn = Carbon::parse('2021-11-10');
            $self->needsMaskingInsNumber = true;
            $self->needsMaskingInsName = true;
            $self->buildLtcsProvisionReportSheetAppendixPdfParamUseCase
                ->allows('handle')
                ->andReturn($self::createLtcsProvisionReportSheetAppendixPdf())
                ->byDefault();
            $self->buildLtcsProvisionReportSheetPdfParamUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self::createLtcsProvisionReportSheetPdf()))
                ->byDefault();
            $self->identifyLtcsInsCardUseCase
                ->allows('handle')
                ->andReturn(Option::some($self->examples->ltcsInsCards[0]))
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->lookupUserUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->users[0]))
                ->byDefault();
            $self->getLtcsProvisionReportUseCase
                ->allows('handle')
                ->andReturn(Option::some($self->examples->ltcsProvisionReports[0]))
                ->byDefault();
            $self->buildLtcsServiceDetailListUseCase
                ->allows('handle')
                ->andReturn($self->examples->ltcsBillingServiceDetails)
                ->byDefault();
            $self->storePdfUseCase
                ->allows('handle')
                ->andReturn('path/to/stored-file.pdf')
                ->byDefault();
            $self->resolveLtcsNameFromServiceCodesUseCase
                ->allows('handle')
                ->andReturn(
                    Seq::from(...$self->examples->ltcsHomeVisitLongTermCareDictionaryEntries)
                        ->toMap(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->serviceCode->toString())
                        ->mapValues(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->name)
                )
                ->byDefault();
            $self->interactor = app(GenerateLtcsProvisionReportSheetPdfInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should('use LookupOfficeUseCase', function (): void {
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->with($this->context, [Permission::updateLtcsProvisionReports()], $this->officeId)
                ->andReturn(Seq::from($this->examples->offices[0]));

            $this->interactor->handle(
                $this->context,
                $this->officeId,
                $this->userId,
                $this->providedIn,
                $this->issuedOn,
                $this->needsMaskingInsNumber,
                $this->needsMaskingInsName,
            );
        });

        $this->should('throw NotFoundException when LookupOfficeUseCase return empty', function (): void {
            $this->lookupOfficeUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    $this->officeId,
                    $this->userId,
                    $this->providedIn,
                    $this->issuedOn,
                    $this->needsMaskingInsNumber,
                    $this->needsMaskingInsName,
                );
            });
        });

        $this->should('use LookupUserUseCase', function (): void {
            $this->lookupUserUseCase
                ->expects('handle')
                ->with($this->context, Permission::updateLtcsProvisionReports(), $this->userId)
                ->andReturn(Seq::from($this->examples->users[0]));

            $this->interactor->handle(
                $this->context,
                $this->officeId,
                $this->userId,
                $this->providedIn,
                $this->issuedOn,
                $this->needsMaskingInsNumber,
                $this->needsMaskingInsName,
            );
        });

        $this->should('throw NotFoundException when LookupUserUseCase return empty', function (): void {
            $this->lookupUserUseCase
                ->expects('handle')
                ->andReturn(Seq::empty());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    $this->officeId,
                    $this->userId,
                    $this->providedIn,
                    $this->issuedOn,
                    $this->needsMaskingInsNumber,
                    $this->needsMaskingInsName,
                );
            });
        });

        $this->should('use IdentifyLtcsInsCardUseCase twice', function (): void {
            $this->identifyLtcsInsCardUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->users[0], equalTo($this->providedIn->firstOfMonth()))
                ->andReturn(Option::some($this->examples->ltcsInsCards[0]));
            $this->identifyLtcsInsCardUseCase
                ->expects('handle')
                ->with($this->context, $this->examples->users[0], equalTo($this->providedIn->lastOfMonth()))
                ->andReturn(Option::some($this->examples->ltcsInsCards[0]));

            $this->interactor->handle(
                $this->context,
                $this->officeId,
                $this->userId,
                $this->providedIn,
                $this->issuedOn,
                $this->needsMaskingInsNumber,
                $this->needsMaskingInsName,
            );
        });

        $this->should('throw NotFoundException when IdentifyLtcsInsCardUseCase return none', function (): void {
            $this->identifyLtcsInsCardUseCase
                ->expects('handle')
                ->andReturn(Option::none())
                ->twice();

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    $this->officeId,
                    $this->userId,
                    $this->providedIn,
                    $this->issuedOn,
                    $this->needsMaskingInsNumber,
                    $this->needsMaskingInsName,
                );
            });
        });

        $this->should('use GetLtcsProvisionReportUseCase', function (): void {
            $this->getLtcsProvisionReportUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    Permission::updateLtcsProvisionReports(),
                    $this->officeId,
                    $this->userId,
                    $this->providedIn
                )
                ->andReturn(Option::some($this->examples->ltcsProvisionReports[0]));

            $this->interactor->handle(
                $this->context,
                $this->officeId,
                $this->userId,
                $this->providedIn,
                $this->issuedOn,
                $this->needsMaskingInsNumber,
                $this->needsMaskingInsName,
            );
        });

        $this->should('throw NotFoundException when GetLtcsProvisionReportUseCase return none', function (): void {
            $this->getLtcsProvisionReportUseCase
                ->expects('handle')
                ->andReturn(Option::none());

            $this->assertThrows(NotFoundException::class, function (): void {
                $this->interactor->handle(
                    $this->context,
                    $this->officeId,
                    $this->userId,
                    $this->providedIn,
                    $this->issuedOn,
                    $this->needsMaskingInsNumber,
                    $this->needsMaskingInsName,
                );
            });
        });

        $this->should('use BuildLtcsServiceDetailListUseCase ForPlan is false', function (): void {
            $this->buildLtcsServiceDetailListUseCase
                ->expects('handle')
                ->with($this->context, $this->providedIn, equalTo(Seq::from($this->examples->ltcsProvisionReports[0])), equalTo(Seq::from($this->examples->users[0])), false)
                ->andReturn($this->examples->ltcsBillingServiceDetails);

            $this->interactor->handle(
                $this->context,
                $this->officeId,
                $this->userId,
                $this->providedIn,
                $this->issuedOn,
                $this->needsMaskingInsNumber,
                $this->needsMaskingInsName,
            );
        });

        $this->should('use BuildLtcsServiceDetailListUseCase ForPlan is true', function (): void {
            $this->buildLtcsServiceDetailListUseCase
                ->expects('handle')
                ->with($this->context, $this->providedIn, equalTo(Seq::from($this->examples->ltcsProvisionReports[0])), equalTo(Seq::from($this->examples->users[0])), true)
                ->andReturn($this->examples->ltcsBillingServiceDetails);

            $this->interactor->handle(
                $this->context,
                $this->officeId,
                $this->userId,
                $this->providedIn,
                $this->issuedOn,
                $this->needsMaskingInsNumber,
                $this->needsMaskingInsName,
            );
        });

        $this->specify('サービスコード名称を取得する', function (): void {
            $this->resolveLtcsNameFromServiceCodesUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    equalTo(
                        Seq::fromArray($this->examples->ltcsBillingServiceDetails)
                            ->map(fn (LtcsBillingServiceDetail $x): ServiceCode => $x->serviceCode)
                            ->distinctBy(fn (ServiceCode $x): string => $x->toString())
                            ->computed()
                    ),
                    $this->providedIn
                )
                ->andReturn(
                    Seq::from(...$this->examples->ltcsHomeVisitLongTermCareDictionaryEntries)
                        ->toMap(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->serviceCode->toString())
                        ->mapValues(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->name)
                );

            $this->interactor->handle(
                $this->context,
                $this->officeId,
                $this->userId,
                $this->providedIn,
                $this->issuedOn,
                $this->needsMaskingInsNumber,
                $this->needsMaskingInsName,
            );
        });

        $this->should('use BuildLtcsProvisionReportSheetAppendixPdfParamUseCase', function (): void {
            $this->buildLtcsProvisionReportSheetAppendixPdfParamUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    $this->examples->ltcsProvisionReports[0],
                    equalTo(Option::from($this->examples->ltcsInsCards[0])),
                    $this->examples->ltcsInsCards[0],
                    $this->examples->offices[0],
                    $this->examples->users[0],
                    equalTo(Seq::fromArray($this->examples->ltcsBillingServiceDetails)),
                    equalTo(
                        Seq::from(...$this->examples->ltcsHomeVisitLongTermCareDictionaryEntries)
                            ->toMap(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->serviceCode->toString())
                            ->mapValues(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->name)
                    ),
                    $this->needsMaskingInsNumber,
                    $this->needsMaskingInsName,
                )
                ->andReturn($this->createLtcsProvisionReportSheetAppendixPdf());
            $this->interactor->handle(
                $this->context,
                $this->officeId,
                $this->userId,
                $this->providedIn,
                $this->issuedOn,
                $this->needsMaskingInsNumber,
                $this->needsMaskingInsName,
            );
        });

        $this->should('use buildLtcsProvisionReportSheetPdfParamUseCase', function (): void {
            $this->buildLtcsProvisionReportSheetPdfParamUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    equalTo(Option::from($this->examples->ltcsInsCards[0])),
                    $this->examples->ltcsInsCards[0],
                    equalTo(Seq::fromArray($this->examples->ltcsBillingServiceDetails)),
                    equalTo(Seq::fromArray($this->examples->ltcsBillingServiceDetails)),
                    $this->examples->users[0],
                    $this->issuedOn,
                    $this->examples->ltcsProvisionReports[0],
                    $this->examples->offices[0],
                    equalTo(
                        Seq::from(...$this->examples->ltcsHomeVisitLongTermCareDictionaryEntries)
                            ->toMap(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->serviceCode->toString())
                            ->mapValues(fn (LtcsHomeVisitLongTermCareDictionaryEntry $x): string => $x->name)
                    ),
                    $this->needsMaskingInsNumber,
                    $this->needsMaskingInsName,
                )
                ->andReturn(Seq::from(self::createLtcsProvisionReportSheetPdf()));

            $this->interactor->handle(
                $this->context,
                $this->officeId,
                $this->userId,
                $this->providedIn,
                $this->issuedOn,
                $this->needsMaskingInsNumber,
                $this->needsMaskingInsName,
            );
        });

        $this->should('use StorePdfUseCase', function (): void {
            $params = [
                'mains' => Seq::from($this->createLtcsProvisionReportSheetPdf()),
                'appendix' => $this->createLtcsProvisionReportSheetAppendixPdf(),
            ];
            $template = 'pdfs.ltcs-provision-report-sheet.index';
            $this->storePdfUseCase
                ->expects('handle')
                ->with(
                    $this->context,
                    'exported',
                    $template,
                    equalTo(['sheet' => $params]),
                    'Landscape'
                )
                ->andReturn('path/to/stored-file.pdf');

            $this->interactor->handle(
                $this->context,
                $this->officeId,
                $this->userId,
                $this->providedIn,
                $this->issuedOn,
                $this->needsMaskingInsNumber,
                $this->needsMaskingInsName,
            );
        });

        $this->should('return filepath', function (): void {
            $this->assertSame(
                'path/to/stored-file.pdf',
                $this->interactor->handle(
                    $this->context,
                    $this->officeId,
                    $this->userId,
                    $this->providedIn,
                    $this->issuedOn,
                    $this->needsMaskingInsNumber,
                    $this->needsMaskingInsName,
                ),
            );
        });
    }

    /**
     * TODO とりあえず適当に用意。多分最終的にはいらない
     *
     * @return \Domain\ProvisionReport\LtcsProvisionReportSheetPdf
     */
    private static function createLtcsProvisionReportSheetPdf(): LtcsProvisionReportSheetPdf
    {
        return LtcsProvisionReportSheetPdf::create(
            [
                'status' => LtcsInsCardStatus::applied(),
                'providedIn' => Carbon::now(),
                'insurerNumber' => '123456',
                'insurerName' => '新宿区',
                'carePlanAuthorOfficeName' => 'テストケア',
                'careManagerName' => 'テスト太郎',
                'carePlanAuthorOfficeTel' => '03-1234-5678',
                'createdOn' => Carbon::now()->toJapaneseDate(),
                'insNumber' => '0123456789',
                'phoneticDisplayName' => 'テストナマエ',
                'displayName' => 'テスト名前',
                'birthday' => Carbon::parse('2001-10-11'),
                'sex' => Sex::female(),
                'ltcsLevel' => LtcsLevel::resolve(LtcsLevel::careLevel2()),
                'maxBenefit' => 36127,
                'copayActivatedOn' => Carbon::now()->toJapaneseYearMonth(),
                'copayDeactivatedOn' => Carbon::now()->toJapaneseYearMonth(),
                'entries' => [
                    [
                        'slot' => TimeRange::create([
                            'start' => '08:00',
                            'end' => '16:00',
                        ]),
                        'serviceName' => '仮のサービス内容',
                        'officeName' => '土屋訪問介護事業所',
                        'plans' => [0, 1, 0, 1],
                        'results' => [0, 1, 0, 1],
                        'plansCount' => 1,
                        'resultsCount' => 2,
                    ],
                    [
                        'slot' => TimeRange::create([
                            'start' => '08:00',
                            'end' => '16:00',
                        ]),
                        'serviceName' => '仮のサービス内容',
                        'officeName' => '土屋訪問介護事業所',
                        'plans' => [0, 1, 0, 0, 0, 1],
                        'results' => [0, 1, 0, 0, 0, 1],
                        'plansCount' => 2,
                        'resultsCount' => 2,
                    ],
                ],
            ]
        );
    }

    /**
     * TODO とりあえず適当に用意。多分最終的にはいらない
     *
     * @return \Domain\ProvisionReport\LtcsProvisionReportSheetAppendixPdf
     */
    private static function createLtcsProvisionReportSheetAppendixPdf(): LtcsProvisionReportSheetAppendixPdf
    {
        return new LtcsProvisionReportSheetAppendixPdf(
            providedIn: Carbon::now(),
            insNumber: '0123456789',
            userName: 'テスト名前',
            entries: [
                new LtcsProvisionReportSheetAppendixPdfEntry(
                    officeName: '土屋訪問介護事業所 新宿', // 事業所名
                    officeCode: '1370406140', // 事業所番号
                    serviceName: '身体介護1', // サービス内容/種類
                    serviceCode: '111111', // サービスコード
                    unitScore: number_format(250), // 単位数
                    count: '8', // 回数
                    wholeScore: number_format(2000), // サービス単位数/金額
                    maxBenefitQuotaExcessScore: number_format(0),
                    maxBenefitExcessScore: number_format(0),
                    scoreWithinMaxBenefitQuota: number_format(0),
                    scoreWithinMaxBenefit: number_format(2000), // 区分支給限度基準内単位数
                    unitCost: sprintf('%.2f', 114000 / 10000), // 単位数単価
                    totalFeeForInsuranceOrBusiness: number_format(0), // 費用総額(保険/事業対象分)
                    benefitRate: '0', // 給付率(%)
                    claimAmountForInsuranceOrBusiness: number_format(0), // 保険/事業費請求額
                    copayForInsuranceOrBusiness: number_format(0), // 利用者負担(保険/事業対象分)
                    copayWholeExpense: number_format(0), // 利用者負担(全額負担分)
                ),
            ],
            maxBenefit: number_format(36127),
            totalScoreTotal: number_format(10000),
            maxBenefitExcessScoreTotal: number_format(10000),
            scoreWithinMaxBenefitTotal: number_format(10000),
            totalFeeForInsuranceOrBusinessTotal: number_format(10000),
            claimAmountForInsuranceOrBusinessTotal: number_format(10000),
            copayForInsuranceOrBusinessTotal: number_format(10000),
            copayWholeExpenseTotal: number_format(10000),
            insuranceClaimAmount: number_format(10000),
            subsidyClaimAmount: number_format(10000),
            copayAmount: number_format(10000),
            unitCost: number_format(10000),
        );
    }
}
