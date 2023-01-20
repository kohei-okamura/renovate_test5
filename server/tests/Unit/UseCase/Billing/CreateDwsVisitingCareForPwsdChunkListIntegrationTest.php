<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\UseCase\Billing;

use App\Providers\AppServiceProvider;
use Domain\Billing\DwsVisitingCareForPwsdChunk;
use Domain\Billing\DwsVisitingCareForPwsdChunkImpl;
use Domain\Billing\DwsVisitingCareForPwsdFragment;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Schedule;
use Domain\ProvisionReport\DwsProvisionReportItem;
use Domain\ServiceCodeDictionary\DwsServiceCodeCategory;
use Illuminate\Support\Arr;
use Lib\Json;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Test;
use UseCase\Billing\CreateDwsVisitingCareForPwsdChunkListInteractor;

/**
 * {@link \UseCase\Billing\CreateDwsVisitingCareForPwsdChunkListInteractor} 関連の統合テスト.
 *
 * `handle()` を呼び出し、実際に処理させた結果を得る
 */
final class CreateDwsVisitingCareForPwsdChunkListIntegrationTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use UnitSupport;

    private CreateDwsVisitingCareForPwsdChunkListInteractor $interactor;

    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (): void {
            $app = app();
            $provider = new AppServiceProvider($app);
            $provider->register();
        });
        self::beforeEachSpec(function (self $self): void {
            $self->interactor = app(CreateDwsVisitingCareForPwsdChunkListInteractor::class);
        });
    }

    /**
     * ProvisionReportItem からのテスト.
     *
     * @test
     * @return void
     */
    public function test_fromProvisionReportItem(): void
    {
        $examples = [
            'Service with whole day' => [
                'provisionReportItems' => [
                    [
                        'start' => ['day' => 1, 'hour' => 21, 'minute' => 00],
                        'end' => ['day' => 2, 'hour' => 9, 'minute' => 00],
                    ],
                    [
                        'start' => ['day' => 2, 'hour' => 9, 'minute' => 00],
                        'end' => ['day' => 2, 'hour' => 21, 'minute' => 00],
                    ],
                    [
                        'start' => ['day' => 2, 'hour' => 21, 'minute' => 00],
                        'end' => ['day' => 3, 'hour' => 8, 'minute' => 45],
                    ],
                ],
                'expectChunks' => [
                    [
                        'start' => ['day' => 1, 'hour' => 21, 'minute' => 00],
                        'end' => ['day' => 2, 'hour' => 0, 'minute' => 00],
                        'fragments' => [
                            [
                                'start' => ['day' => 1, 'hour' => 21, 'minute' => 00],
                                'end' => ['day' => 2, 'hour' => 0, 'minute' => 00],
                            ],
                        ],
                    ],
                    [
                        'start' => ['day' => 2, 'hour' => 0, 'minute' => 00],
                        'end' => ['day' => 3, 'hour' => 0, 'minute' => 00],
                        'fragments' => [
                            [
                                'start' => ['day' => 2, 'hour' => 0, 'minute' => 00],
                                'end' => ['day' => 3, 'hour' => 0, 'minute' => 00],
                            ],
                        ],
                    ],
                    [
                        'start' => ['day' => 3, 'hour' => 0, 'minute' => 00],
                        'end' => ['day' => 3, 'hour' => 8, 'minute' => 45],
                        'fragments' => [
                            [
                                'start' => ['day' => 3, 'hour' => 0, 'minute' => 00],
                                'end' => ['day' => 3, 'hour' => 8, 'minute' => 45],
                            ],
                        ],
                    ],
                ],
            ],
            'Service with whole day and input order' => [
                'provisionReportItems' => [
                    [
                        'start' => ['day' => 22, 'hour' => 9, 'minute' => 00],
                        'end' => ['day' => 22, 'hour' => 21, 'minute' => 00],
                    ],
                    [
                        'start' => ['day' => 22, 'hour' => 21, 'minute' => 00],
                        'end' => ['day' => 23, 'hour' => 8, 'minute' => 45],
                    ],
                    [
                        'start' => ['day' => 21, 'hour' => 21, 'minute' => 00],
                        'end' => ['day' => 22, 'hour' => 9, 'minute' => 0],
                    ],
                ],
                'expectChunks' => [
                    [
                        'start' => ['day' => 21, 'hour' => 21, 'minute' => 00],
                        'end' => ['day' => 22, 'hour' => 0, 'minute' => 00],
                        'fragments' => [
                            [
                                'start' => ['day' => 21, 'hour' => 21, 'minute' => 00],
                                'end' => ['day' => 22, 'hour' => 0, 'minute' => 00],
                            ],
                        ],
                    ],
                    [
                        'start' => ['day' => 22, 'hour' => 0, 'minute' => 00],
                        'end' => ['day' => 23, 'hour' => 0, 'minute' => 00],
                        'fragments' => [
                            [
                                'start' => ['day' => 22, 'hour' => 0, 'minute' => 00],
                                'end' => ['day' => 23, 'hour' => 0, 'minute' => 00],
                            ],
                        ],
                    ],
                    [
                        'start' => ['day' => 23, 'hour' => 0, 'minute' => 00],
                        'end' => ['day' => 23, 'hour' => 8, 'minute' => 45],
                        'fragments' => [
                            [
                                'start' => ['day' => 23, 'hour' => 0, 'minute' => 00],
                                'end' => ['day' => 23, 'hour' => 8, 'minute' => 45],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->should('run with visitingCareForPwsd3', function (array $provisionReportItems, array $expectChunks) {
            // 入力値組み立て
            $baseItem = $this->examples->dwsProvisionReports[1]->results[0]->copy([
                'headcount' => 1,
                'movingDurationMinutes' => 0,
                'options' => [],
            ]);
            $results = Seq::fromArray($provisionReportItems)
                ->map(fn (array $provisionReport): DwsProvisionReportItem => $baseItem->copy([
                    'schedule' => Schedule::create([
                        'date' => Carbon::create(2021, 2, Arr::get($provisionReport, 'start.day')),
                        'start' => Carbon::create(
                            2021,
                            2,
                            Arr::get($provisionReport, 'start.day'),
                            Arr::get($provisionReport, 'start.hour'),
                            Arr::get($provisionReport, 'start.minute'),
                        ),
                        'end' => Carbon::create(
                            2021,
                            2,
                            Arr::get($provisionReport, 'end.day'),
                            Arr::get($provisionReport, 'end.hour'),
                            Arr::get($provisionReport, 'end.minute'),
                        ),
                    ]),
                ]))
                ->toArray();
            $provisionReport = $this->examples->dwsProvisionReports[0]->copy([
                ...compact('results'),
                'providedIn' => Carbon::create(2021, 2),
                'fixedAt' => Carbon::create(2021, 3, 5),
            ]);

            // 期待値の組み立て
            $certification = $this->examples->dwsCertifications[10];
            $baseChunk = DwsVisitingCareForPwsdChunkImpl::create([
                'userId' => $this->examples->dwsProvisionReports[0]->userId,
                'category' => DwsServiceCodeCategory::visitingCareForPwsd3(),
                'isEmergency' => false,
                'isFirst' => false,
                'isBehavioralDisorderSupportCooperation' => false,
            ]);
            $baseFragment = DwsVisitingCareForPwsdFragment::create([
                'isHospitalized' => false,
                'isLongHospitalized' => false,
                'isCoaching' => false,
                'isSecondary' => false,
                'headcount' => 1,
            ]);
            $expectChunkArray = Seq::fromArray($expectChunks)
                ->map(function (array $x) use ($baseChunk, $baseFragment): DwsVisitingCareForPwsdChunk {
                    $fragments = Seq::fromArray($x['fragments'])->map(
                        fn (array $xf): DwsVisitingCareForPwsdFragment => $baseFragment->copy([
                            'isMoving' => Arr::get($xf, 'isMoving', false),
                            'movingDurationMinutes' => Arr::get($xf, 'movingDurationMinutes', 0),
                            'range' => CarbonRange::create([
                                'start' => Carbon::create(
                                    2021,
                                    2,
                                    Arr::get($xf, 'start.day'),
                                    Arr::get($xf, 'start.hour'),
                                    Arr::get($xf, 'start.minute'),
                                ),
                                'end' => Carbon::create(
                                    2021,
                                    2,
                                    Arr::get($xf, 'end.day'),
                                    Arr::get($xf, 'end.hour'),
                                    Arr::get($xf, 'end.minute'),
                                ),
                            ]),
                        ])
                    );
                    return $baseChunk->copy([
                        ...compact('fragments'),
                        'providedOn' => Carbon::create(2021, 2, Arr::get($x, 'start.day')),
                        'range' => CarbonRange::create([
                            'start' => Carbon::create(
                                2021,
                                2,
                                Arr::get($x, 'start.day'),
                                Arr::get($x, 'start.hour'),
                                Arr::get($x, 'start.minute'),
                            ),
                            'end' => Carbon::create(
                                2021,
                                2,
                                Arr::get($x, 'end.day'),
                                Arr::get($x, 'end.hour'),
                                Arr::get($x, 'end.minute'),
                            ),
                        ]),
                    ]);
                })
                ->toArray();

            $chunks = $this->interactor->handle($this->context, $certification, $provisionReport);

            $this->assertSame(count($expectChunks), $chunks->count(), Json::encode($chunks->toArray()));
            foreach ($chunks as $index => $chunk) {
                assert($chunk instanceof DwsVisitingCareForPwsdChunkImpl);

                /** @var \Domain\Billing\DwsVisitingCareForPwsdChunkImpl $expected */
                $expected = $expectChunkArray[$index];
                $this->assertModelStrictEquals($expected, $chunk->copy(['id' => null]));
                $this->assertSame(
                    $expected->fragments->count(),
                    $chunk->fragments->count(),
                    Json::encode($chunk->fragments)
                );
                $this->assertForAll($chunk->getDurations(), fn (array $x): bool => count($x) > 0);
            }
        }, compact('examples'));
    }
}
