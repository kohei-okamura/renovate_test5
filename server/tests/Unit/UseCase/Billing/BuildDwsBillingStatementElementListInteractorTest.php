<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Billing\DwsServiceDivisionCode;
use Domain\Common\Carbon;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryCsv;
use Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryCsv;
use Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry;
use Lib\Csv;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DummyContextMixin;
use Tests\Unit\Mixins\DwsHomeHelpServiceDictionaryEntryFinderMixin;
use Tests\Unit\Mixins\DwsVisitingCareForPwsdDictionaryEntryFinderMixin;
use Tests\Unit\Mixins\IdentifyDwsHomeHelpServiceDictionaryUseCaseMixin;
use Tests\Unit\Mixins\IdentifyDwsVisitingCareForPwsdDictionaryUseCaseMixin;
use Tests\Unit\Test;
use UseCase\Billing\BuildDwsBillingStatementElementListInteractor;

/**
 * {@link \UseCase\Billing\BuildDwsBillingStatementElementListInteractor} Test.
 */
final class BuildDwsBillingStatementElementListInteractorTest extends Test
{
    use DummyContextMixin;
    use DwsBillingTestSupport;
    use DwsHomeHelpServiceDictionaryEntryFinderMixin;
    use DwsVisitingCareForPwsdDictionaryEntryFinderMixin;
    use IdentifyDwsHomeHelpServiceDictionaryUseCaseMixin;
    use IdentifyDwsVisitingCareForPwsdDictionaryUseCaseMixin;
    use MatchesSnapshots;
    use UnitSupport;

    /** @var \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry[]|\ScalikePHP\Seq */
    private Seq $homeHelpServiceDictionaryEntries;

    /** @var \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry[]|\ScalikePHP\Seq */
    private Seq $visitingCareForPwsdDictionaryEntries;

    private BuildDwsBillingStatementElementListInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->setupTestData();
            $self->homeHelpServiceDictionaryEntries = Seq::from(...$self->homeHelpServiceDictionaryEntries());
            $self->visitingCareForPwsdDictionaryEntries = Seq::from(...$self->visitingCareForPwsdDictionaryEntries());
        });
        self::beforeEachSpec(function (self $self): void {
            $self->identifyDwsHomeHelpServiceDictionaryUseCase
                ->allows('handle')
                ->andReturn(Option::some($self->homeHelpServiceDictionary))
                ->byDefault();

            $self->identifyDwsVisitingCareForPwsdDictionaryUseCase
                ->allows('handle')
                ->andReturn(Option::some($self->visitingCareForPwsdDictionary))
                ->byDefault();

            $self->dwsHomeHelpServiceDictionaryEntryFinder
                ->allows('findByCategory')
                ->andReturnUsing(
                    fn (
                        Carbon $providedIn,
                        DwsServiceCodeCategory $category
                    ): DwsHomeHelpServiceDictionaryEntry => $self
                        ->homeHelpServiceDictionaryEntries
                        ->find(fn (DwsHomeHelpServiceDictionaryEntry $x): bool => $x->category === $category)
                        ->get()
                )
                ->byDefault();

            $self->dwsVisitingCareForPwsdDictionaryEntryFinder
                ->allows('findByCategory')
                ->andReturnUsing(
                    fn (
                        Carbon $providedIn,
                        DwsServiceCodeCategory $category
                    ): DwsVisitingCareForPwsdDictionaryEntry => $self
                        ->visitingCareForPwsdDictionaryEntries
                        ->find(fn (DwsVisitingCareForPwsdDictionaryEntry $x): bool => $x->category === $category)
                        ->get()
                )
                ->byDefault();

            $self->interactor = app(BuildDwsBillingStatementElementListInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should(
            'return a Seq of DwsBillingStatementItem',
            function (Seq $details): void {
                $actual = $this->interactor->handle(
                    $this->context,
                    $this->homeHelpServiceCalcSpec,
                    $this->visitingCareForPwsdCalcSpec,
                    true,
                    $this->providedIn,
                    $details
                );

                $this->assertInstanceOf(Seq::class, $actual);
                $this->assertMatchesModelSnapshot($actual);
            },
            [
                'examples' => [
                    [$this->serviceDetails(DwsServiceDivisionCode::homeHelpService(), $this->providedIn)],
                    [$this->serviceDetails(DwsServiceDivisionCode::visitingCareForPwsd(), $this->providedIn)],
                ],
            ]
        );
    }

    /**
     * テスト用の障害福祉サービス：居宅介護：サービスコード辞書エントリ一覧を生成する.
     *
     * @return \Domain\ServiceCodeDictionary\DwsHomeHelpServiceDictionaryEntry[]|iterable
     */
    private function homeHelpServiceDictionaryEntries(): iterable
    {
        $id = 1;
        $csv = codecept_data_dir('Billing/dws-home-help-service-dictionary-for-statement.csv');
        $data = Csv::read($csv);
        foreach (DwsHomeHelpServiceDictionaryCsv::create($data)->rows() as $row) {
            yield $row->toDictionaryEntry(['id' => $id++]);
        }
    }

    /**
     * テスト用の障害福祉サービス：重度訪問介護：サービスコード辞書エントリ一覧を生成する.
     *
     * @return \Domain\ServiceCodeDictionary\DwsVisitingCareForPwsdDictionaryEntry[]|iterable
     */
    private function visitingCareForPwsdDictionaryEntries(): iterable
    {
        $id = 1;
        $csv = codecept_data_dir('Billing/dws-visiting-care-for-pwsd-dictionary-for-statement.csv');
        $data = Csv::read($csv);
        foreach (DwsVisitingCareForPwsdDictionaryCsv::create($data)->rows() as $row) {
            yield $row->toDictionaryEntry(['id' => $id++]);
        }
    }
}
