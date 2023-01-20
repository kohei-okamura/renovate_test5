<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use Domain\Common\Carbon;
use Domain\DwsCertification\DwsCertification;
use Domain\DwsCertification\DwsCertificationAgreementType;
use Domain\DwsCertification\DwsLevel;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\DummyContextMixin;
use Tests\Unit\Test;
use UseCase\Billing\BuildDwsBillingStatementContractListInteractor;

/**
 * {@link \UseCase\Billing\BuildDwsBillingStatementContractListInteractor} のテスト.
 */
final class BuildDwsBillingStatementContractListInteractorTest extends Test
{
    use DummyContextMixin;
    use DwsBillingTestSupport;
    use MatchesSnapshots;
    use UnitSupport;

    private BuildDwsBillingStatementContractListInteractor $interactor;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->setupTestData();
        });
        self::beforeEachSpec(function (self $self): void {
            $self->interactor = app(BuildDwsBillingStatementContractListInteractor::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_handle(): void
    {
        $this->should(
            'return a Seq of DwsBillingStatementContract',
            function (DwsCertification $certification): void {
                $providedIn = Carbon::create(2021, 2);
                $actual = $this->interactor->handle($this->context, $this->office, $certification, $providedIn);
                $this->assertMatchesModelSnapshot($actual);
            },
            ['examples' => $this->examples()]
        );
    }

    /**
     * テスト用のデータを生成する.
     *
     * @return array|array[]|\Domain\DwsCertification\DwsCertification[][]
     */
    private function examples(): array
    {
        return [
            'general case' => [
                $this->dwsCertification($this->office, $this->user, [
                    'agreements' => [
                        $this->dwsCertificationAgreement(1, [
                            'officeId' => $this->office->id,
                            'dwsCertificationAgreementType' => DwsCertificationAgreementType::physicalCare(),
                            'paymentAmount' => 6000, // 100時間＝6,000分
                        ]),
                        $this->dwsCertificationAgreement(2, [
                            'officeId' => $this->office->id,
                            'dwsCertificationAgreementType' => DwsCertificationAgreementType::housework(),
                            'paymentAmount' => 1200, // 20時間＝1,200分
                        ]),
                    ],
                ]),
            ],
            'paymentAmount changed at start of month' => [
                $this->dwsCertification($this->office, $this->user, [
                    'dwsLevel' => DwsLevel::level4(),
                    'agreements' => [
                        $this->dwsCertificationAgreement(1, [
                            'officeId' => $this->office->id,
                            'dwsCertificationAgreementType' => DwsCertificationAgreementType::visitingCareForPwsd3(),
                            'paymentAmount' => 23040, // 16日＝384時間＝23,040分
                            'agreedOn' => Carbon::create(2021, 1, 16),
                            'expiredOn' => Carbon::create(2021, 1, 31),
                        ]),
                        $this->dwsCertificationAgreement(2, [
                            'officeId' => $this->office->id,
                            'dwsCertificationAgreementType' => DwsCertificationAgreementType::visitingCareForPwsd3(),
                            'paymentAmount' => 43200, // 30日＝720時間＝43,200分
                            'agreedOn' => Carbon::create(2021, 2, 1),
                        ]),
                    ],
                ]),
            ],
            'paymentAmount changed in month' => [
                $this->dwsCertification($this->office, $this->user, [
                    'dwsLevel' => DwsLevel::level6(),
                    'agreements' => [
                        $this->dwsCertificationAgreement(1, [
                            'officeId' => $this->office->id,
                            'dwsCertificationAgreementType' => DwsCertificationAgreementType::visitingCareForPwsd2(),
                            'paymentAmount' => 14400, // 10日＝240時間＝14,400分
                            'agreedOn' => Carbon::create(2021, 1, 1),
                            'expiredOn' => Carbon::create(2021, 2, 10),
                        ]),
                        $this->dwsCertificationAgreement(2, [
                            'officeId' => $this->office->id,
                            'dwsCertificationAgreementType' => DwsCertificationAgreementType::visitingCareForPwsd2(),
                            'paymentAmount' => 25920, // 18日＝432時間＝25,920分
                            'agreedOn' => Carbon::create(2021, 2, 11),
                            'expiredOn' => Carbon::create(2021, 2, 28),
                        ]),
                    ],
                ]),
            ],
            'paymentAmount changed at start of next month' => [
                $this->dwsCertification($this->office, $this->user, [
                    'dwsLevel' => DwsLevel::level6(),
                    'isSubjectOfComprehensiveSupport' => true,
                    'agreements' => [
                        $this->dwsCertificationAgreement(1, [
                            'officeId' => $this->office->id,
                            'dwsCertificationAgreementType' => DwsCertificationAgreementType::visitingCareForPwsd1(),
                            'paymentAmount' => 21600, // 15日＝360時間＝21,600分
                            'agreedOn' => Carbon::create(2021, 1, 1),
                            'expiredOn' => Carbon::create(2021, 2, 28),
                        ]),
                        $this->dwsCertificationAgreement(2, [
                            'officeId' => $this->office->id,
                            'dwsCertificationAgreementType' => DwsCertificationAgreementType::visitingCareForPwsd1(),
                            'paymentAmount' => 43200, // 30日＝720時間＝43,200分
                            'agreedOn' => Carbon::create(2021, 3, 1),
                        ]),
                    ],
                ]),
            ],
            'Multiple different Offices' => [
                $this->dwsCertification($this->office, $this->user, [
                    'agreements' => [
                        $this->dwsCertificationAgreement(1, [
                            'officeId' => $this->office->id,
                            'dwsCertificationAgreementType' => DwsCertificationAgreementType::physicalCare(),
                            'paymentAmount' => 6000, // 100時間＝6,000分
                        ]),
                        $this->dwsCertificationAgreement(2, [
                            'officeId' => 1,
                            'dwsCertificationAgreementType' => DwsCertificationAgreementType::housework(),
                            'paymentAmount' => 1200, // 20時間＝1,200分
                        ]),
                    ],
                ]),
            ],
        ];
    }
}
