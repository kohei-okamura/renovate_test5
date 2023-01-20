<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateDwsContractRequest;
use Closure;
use Domain\Billing\DwsServiceDivisionCode;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\Common\ServiceSegment;
use Domain\Context\Context;
use Domain\Contract\ContractStatus;
use Domain\FinderResult;
use Domain\Permission\Permission;
use Generator;
use Lib\Arrays;
use Lib\Json;
use Mockery;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\FindDwsProvisionReportUseCaseMixin;
use Tests\Unit\Mixins\GetOverlapContractUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\UpdateDwsContractRequest} のテスト
 */
final class UpdateDwsContractRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use FindDwsProvisionReportUseCaseMixin;
    use GetOverlapContractUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private UpdateDwsContractRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->findDwsProvisionReportUseCase
                ->allows('handle')
                ->andReturn(FinderResult::from(
                    [
                        $self->examples->dwsProvisionReports[0]->copy(['providedIn' => Carbon::parse('2020-10')]),
                        $self->examples->dwsProvisionReports[0]->copy(['providedIn' => Carbon::parse('2020-11')]),
                    ],
                    Pagination::create()
                ))
                ->byDefault();
            $self->getOverlapContractUseCase
                ->allows('handle')
                ->andReturn(Seq::empty())
                ->byDefault();
            $self->getOverlapContractUseCase
                ->allows('handle')
                ->with(
                    anInstanceOf(Context::class),
                    Permission::updateDwsContracts(),
                    $self->examples->users[1]->id,
                    Mockery::any(),
                    ServiceSegment::disabilitiesWelfare(),
                )
                ->andReturn(Seq::from($self->examples->contracts[1]))
                ->byDefault();

            $self->lookupOfficeUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), [Permission::updateDwsContracts()], $self->examples->offices[0]->id)
                ->andReturn(Seq::from($self->examples->offices[0]));
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), [Permission::updateDwsContracts()], self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq());

            $self->request = new UpdateDwsContractRequest();
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::emptySeq(),
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_payload(): void
    {
        $examples = [
            'all elements given' => [
                $this->createInput(),
            ],
            'without contractedOn' => [
                $this->createInput(function (array &$input): void {
                    unset($input['contractedOn']);
                }),
            ],
            'without terminatedOn' => [
                $this->createInput(function (array &$input): void {
                    unset($input['terminatedOn']);
                }),
            ],
            'without dwsPeriods.11.start' => [
                $this->createInput(function (array &$input): void {
                    unset($input['dwsPeriods'][DwsServiceDivisionCode::homeHelpService()->value()]['start']);
                }),
            ],
            'without dwsPeriods.11.end' => [
                $this->createInput(function (array &$input): void {
                    unset($input['dwsPeriods'][DwsServiceDivisionCode::homeHelpService()->value()]['end']);
                }),
            ],
            'without dwsPeriods.12.start' => [
                $this->createInput(function (array &$input): void {
                    unset($input['dwsPeriods'][DwsServiceDivisionCode::visitingCareForPwsd()->value()]['start']);
                }),
            ],
            'without dwsPeriods.12.end' => [
                $this->createInput(function (array &$input): void {
                    unset($input['dwsPeriods'][DwsServiceDivisionCode::visitingCareForPwsd()->value()]['end']);
                }),
            ],
            'without note' => [
                $this->createInput(function (array &$input): void {
                    unset($input['note']);
                }),
            ],
            'when status is provisional' => [
                $this->createInput(function (array &$input): void {
                    $input['status'] = ContractStatus::provisional();
                }),
            ],
            'when status is formal' => [
                $this->createInput(function (array &$input): void {
                    $input['status'] = ContractStatus::formal();
                }),
            ],
            'when status is terminated' => [
                $this->createInput(function (array &$input): void {
                    $input['status'] = ContractStatus::terminated();
                }),
            ],
            'when status is disabled' => [
                $this->createInput(function (array &$input): void {
                    $input['status'] = ContractStatus::disabled();
                }),
            ],
        ];
        $this->should(
            'return array',
            function (array $input): void {
                $this->request->initialize(
                    [],
                    [],
                    [],
                    [],
                    [],
                    ['CONTENT_TYPE' => 'application/json'],
                    Json::encode($input)
                );

                $actual = $this->request->payload();

                $this->assertMatchesModelSnapshot($actual);
            },
            compact('examples')
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_validation(): void
    {
        $this->should(
            'pass',
            function (?Closure $f = null): void {
                $validator = $this->request->createValidatorInstance($this->createInput($f));
                $this->assertTrue($validator->passes());
            },
            ['examples' => iterator_to_array($this->passes())]
        );
        $this->should(
            'fail',
            function (?Closure $f, array $errors): void {
                $validator = $this->request->createValidatorInstance($this->createInput($f));
                $this->assertTrue($validator->fails());
                $this->assertSame($errors, $validator->errors()->toArray());
            },
            ['examples' => iterator_to_array($this->fails())]
        );
    }

    /**
     * 入力値を生成する.
     *
     * @param null|\Closure $f
     * @return array
     */
    private function createInput(?Closure $f = null): array
    {
        $contract = $this->examples->contracts[0];
        $input = [
            'officeId' => $contract->officeId,
            'status' => ContractStatus::terminated()->value(),
            'contractedOn' => '2020-01-01',
            'terminatedOn' => '2020-12-31',
            'dwsPeriods' => [
                DwsServiceDivisionCode::homeHelpService()->value() => [
                    'start' => '2020-01-05',
                    'end' => '2020-05-31',
                ],
                DwsServiceDivisionCode::visitingCareForPwsd()->value() => [
                    'start' => '2020-06-01',
                    'end' => '2020-12-25',
                ],
            ],
            'note' => 'だるまさんが転んだ',

            // URLパラメータ
            'userId' => $contract->userId,
            'id' => $contract->id,
        ];
        if ($f !== null) {
            $f($input);
        }
        return $input;
    }

    /**
     * バリデーションに成功するパターン.
     *
     * @returns \Generator
     */
    private function passes(): Generator
    {
        /** @var \Domain\Contract\ContractStatus[]|\ScalikePHP\Seq $statuses */
        $statuses = Seq::from(...ContractStatus::all());

        /** @var \Domain\Contract\ContractStatus[]|\ScalikePHP\Seq $withoutTerminated */
        $withoutTerminated = $statuses->filter(fn (ContractStatus $x): bool => $x !== ContractStatus::terminated());

        // デフォルト値を検証
        yield 'when the valid input given' => [];

        // 契約日
        yield 'when the status is disabled even if the contractedOn is not given' => [
            function (array &$input): void {
                $input['status'] = ContractStatus::disabled()->value();
                unset($input['contractedOn']);
            },
        ];
        yield 'when the status is disabled even if the contractedOn is empty' => [
            function (array &$input): void {
                $input['status'] = ContractStatus::disabled()->value();
                $input['contractedOn'] = '';
            },
        ];

        // 解約日
        foreach ($withoutTerminated as $status) {
            yield "when the status is {$status->key()} even if the terminatedOn is not given" => [
                function (array &$input) use ($status): void {
                    $input['status'] = $status->value();
                    $input['terminatedOn'] = '';
                },
            ];
        }

        // 初回サービス提供日
        foreach (DwsServiceDivisionCode::all() as $code) {
            $start = "dwsPeriods.{$code->value()}.start";
            yield "when the {$start} equals the contractedOn" => [
                function (array &$input) use ($code): void {
                    $input['dwsPeriods'][$code->value()]['start'] = $input['contractedOn'];
                },
            ];
            yield "when opposite value is given even if the {$start} is not given" => [
                function (array &$input) use ($code): void {
                    unset($input['dwsPeriods'][$code->value()]['start']);
                },
            ];
            yield "when opposite value is given even if the {$start} is empty" => [
                function (array &$input) use ($code): void {
                    $input['dwsPeriods'][$code->value()]['start'] = '';
                },
            ];
        }

        // 最終サービス提供日
        foreach (DwsServiceDivisionCode::all() as $code) {
            $start = "dwsPeriods.{$code->value()}.start";
            $end = "dwsPeriods.{$code->value()}.end";
            foreach ($withoutTerminated as $status) {
                yield "when status is {$status->key()} even if {$end} is not given" => [
                    function (array &$input) use ($code, $status): void {
                        $input['status'] = $status->value();
                        $input['dwsPeriods'][$code->value()]['start'] = '2020-05-17';
                        unset($input['dwsPeriods'][$code->value()]['end']);
                    },
                ];
                yield "when status is {$status->key()} even if {$end} is empty" => [
                    function (array &$input) use ($code, $status): void {
                        $input['status'] = $status->value();
                        $input['dwsPeriods'][$code->value()]['start'] = '2020-05-17';
                        $input['dwsPeriods'][$code->value()]['end'] = '';
                    },
                ];
            }
            yield "when {$start} is empty even if {$end} is not given" => [
                function (array &$input) use ($code): void {
                    $input['status'] = ContractStatus::terminated()->value();
                    $input['dwsPeriods'][$code->value()]['start'] = '';
                    unset($input['dwsPeriods'][$code->value()]['end']);
                },
            ];
            yield "when {$start} is empty even if {$end} is empty" => [
                function (array &$input) use ($code): void {
                    $input['status'] = ContractStatus::terminated()->value();
                    $input['dwsPeriods'][$code->value()]['start'] = '';
                    $input['dwsPeriods'][$code->value()]['end'] = '';
                },
            ];
        }

        // 備考
        yield 'when note is not given' => [
            function (array &$input): void {
                unset($input['note']);
            },
        ];
        yield 'when note is empty' => [
            function (array &$input): void {
                $input['note'] = '';
            },
        ];
        yield 'when the note is not longer than 255 characters' => [
            function (array &$input): void {
                $input['note'] = str_repeat('あ', 255);
            },
        ];
    }

    /**
     * バリデーションに失敗するパターン.
     *
     * @returns \Generator
     */
    private function fails(): Generator
    {
        /** @var \Domain\Contract\ContractStatus[]|\ScalikePHP\Seq $statuses */
        $statuses = Seq::from(...ContractStatus::all());

        /** @var \Domain\Contract\ContractStatus[]|\ScalikePHP\Seq $withoutDisabled */
        $withoutDisabled = $statuses->filter(fn (ContractStatus $x): bool => $x !== ContractStatus::disabled());

        // 事業所 ID
        yield 'when the officeId is not given' => [
            function (array &$input): void {
                unset($input['officeId']);
            },
            ['officeId' => ['入力してください。']],
        ];
        yield 'when the officeId is empty' => [
            function (array &$input): void {
                $input['officeId'] = '';
            },
            ['officeId' => ['入力してください。']],
        ];
        yield 'when the office not exists' => [
            function (array &$input): void {
                $input['officeId'] = self::NOT_EXISTING_ID;
            },
            ['officeId' => ['正しい値を入力してください。']],
        ];

        // 状態
        yield 'when the status is not given' => [
            function (array &$input): void {
                unset($input['status']);
            },
            ['status' => ['入力してください。']],
        ];
        yield 'when the status is empty' => [
            function (array &$input): void {
                $input['status'] = '';
            },
            ['status' => ['入力してください。']],
        ];
        yield 'when the status is not a valid value' => [
            function (array &$input): void {
                $input['status'] = 'あいうえこかきくけこ';
            },
            ['status' => ['契約状態を指定してください。']],
        ];

        // 契約日
        foreach ($withoutDisabled as $status) {
            yield "when the contractedOn is not given if the status is {$status->key()}" => [
                function (array &$input) use ($status): void {
                    $input['status'] = $status->value();
                    unset($input['contractedOn']);
                },
                ['contractedOn' => ['入力してください。']],
            ];
            yield "when the contractedOn is empty if the status is {$status->key()}" => [
                function (array &$input) use ($status): void {
                    $input['status'] = $status->value();
                    $input['contractedOn'] = '';
                },
                ['contractedOn' => ['入力してください。']],
            ];
        }
        yield 'when the contractedOn is not a valid date format' => [
            function (array &$input): void {
                $input['contractedOn'] = '2021年4月12日';
            },
            ['contractedOn' => ['正しい日付を入力してください。']],
        ];
        yield 'when the contractedOn does not exist' => [
            function (array &$input): void {
                $input['contractedOn'] = '1999-02-29';
            },
            ['contractedOn' => ['正しい日付を入力してください。']],
        ];

        // 解約日
        yield 'when the terminatedOn is not given if the status is terminated' => [
            function (array &$input): void {
                $input['status'] = ContractStatus::terminated()->value();
                unset($input['terminatedOn']);
            },
            ['terminatedOn' => ['入力してください。']],
        ];
        yield 'when the terminatedOn is empty if the status is terminated' => [
            function (array &$input): void {
                $input['status'] = ContractStatus::terminated()->value();
                $input['terminatedOn'] = '';
            },
            ['terminatedOn' => ['入力してください。']],
        ];
        yield 'when the terminatedOn is not a valid date format' => [
            function (array &$input): void {
                $input['terminatedOn'] = '2021年4月12日';
            },
            ['terminatedOn' => ['正しい日付を入力してください。']],
        ];
        yield 'when the terminatedOn does not exist' => [
            function (array &$input): void {
                $input['terminatedOn'] = '1999-02-29';
            },
            ['terminatedOn' => ['正しい日付を入力してください。']],
        ];
        yield 'when the terminatedOn equals the contractedOn' => [
            function (array &$input): void {
                $input['terminatedOn'] = $input['contractedOn'];
            },
            ['terminatedOn' => ['契約日以降の日付を入力してください。']],
        ];
        yield 'when the terminatedOn is before the contractedOn' => [
            function (array &$input): void {
                $input['contractedOn'] = '2020-01-01';
                $input['terminatedOn'] = '2019-12-31';
            },
            ['terminatedOn' => ['契約日以降の日付を入力してください。']],
        ];
        yield 'when the terminatedOn is before providedIn of provision reports' => [
            function (array &$input): void {
                $input['status'] = ContractStatus::terminated()->value();
                $input['terminatedOn'] = '2020-10-10';
            },
            ['terminatedOn' => ['解約月より後に登録された予実があるため、処理できませんでした。先に予実を削除してください。']],
        ];

        // 契約の重複バリデーション
        yield 'when a overlapped contract exists' => [
            function (array &$input): void {
                // `userId` が `$this->examples->users[1]->id` である場合のみ重複する契約を返すようモックを設定している
                $input['userId'] = $this->examples->users[1]->id;
                // 更新先のステータスが本契約の場合のみチェックを行う
                $input['status'] = ContractStatus::formal()->value();
            },
            ['contractedOn' => ['重複する契約が既に登録されています。ご確認ください。']],
        ];

        // 初回サービス提供日
        foreach ($withoutDisabled as $status) {
            yield "when all of the dwsPeriods.*.start are not given if the status is {$status->key()}" => [
                function (array &$input) use ($status): void {
                    $input['status'] = $status->value();
                    unset(
                        $input['dwsPeriods'][DwsServiceDivisionCode::homeHelpService()->value()]['start'],
                        $input['dwsPeriods'][DwsServiceDivisionCode::visitingCareForPwsd()->value()]['start']
                    );
                },
                Arrays::generate(function (): iterable {
                    foreach (DwsServiceDivisionCode::all() as $code) {
                        $codeValue = $code->value();
                        yield "dwsPeriods.{$codeValue}.start" => ['入力してください。'];
                    }
                }),
            ];
            yield "when all of the dwsPeriods.*.start are empty if the status is {$status->key()}" => [
                function (array &$input) use ($status): void {
                    $input['status'] = $status->value();
                    $input['dwsPeriods'][DwsServiceDivisionCode::homeHelpService()->value()]['start'] = '';
                    $input['dwsPeriods'][DwsServiceDivisionCode::visitingCareForPwsd()->value()]['start'] = '';
                },
                Arrays::generate(function (): iterable {
                    foreach (DwsServiceDivisionCode::all() as $code) {
                        $codeValue = $code->value();
                        yield "dwsPeriods.{$codeValue}.start" => ['入力してください。'];
                    }
                }),
            ];
        }
        foreach (DwsServiceDivisionCode::all() as $code) {
            $start = "dwsPeriods.{$code->value()}.start";
            yield "when the {$start} is not a valid date format" => [
                function (array &$input) use ($code): void {
                    $input['dwsPeriods'][$code->value()]['start'] = '2021年4月12日';
                },
                [$start => ['正しい日付を入力してください。']],
            ];
            yield "when the {$start} does not exist" => [
                function (array &$input) use ($code): void {
                    $input['dwsPeriods'][$code->value()]['start'] = '1999-02-29';
                },
                [$start => ['正しい日付を入力してください。']],
            ];
            yield "when the {$start} is before the contractedOn" => [
                function (array &$input) use ($code): void {
                    $input['contractedOn'] = '2020-01-01';
                    $input['dwsPeriods'][$code->value()]['start'] = '2019-12-31';
                },
                [$start => ['契約日以降の日付を入力してください。']],
            ];
        }

        // 最終サービス提供日
        foreach (DwsServiceDivisionCode::all() as $code) {
            $start = "dwsPeriods.{$code->value()}.start";
            $end = "dwsPeriods.{$code->value()}.end";
            yield "when the {$end} is not given if the status is terminated and {$start} is specified" => [
                function (array &$input) use ($code): void {
                    $input['status'] = ContractStatus::terminated()->value();
                    $input['dwsPeriods'][$code->value()]['start'] = '2020-05-17';
                    unset($input['dwsPeriods'][$code->value()]['end']);
                },
                [$end => ['入力してください。']],
            ];
            yield "when the {$end} is empty if the status is terminated and {$start} is specified" => [
                function (array &$input) use ($code): void {
                    $input['status'] = ContractStatus::terminated()->value();
                    $input['dwsPeriods'][$code->value()]['start'] = '2020-05-17';
                    $input['dwsPeriods'][$code->value()]['end'] = '';
                },
                [$end => ['入力してください。']],
            ];
            yield "when the {$end} is not a valid date format" => [
                function (array &$input) use ($code): void {
                    $input['dwsPeriods'][$code->value()]['end'] = '2021年4月12日';
                },
                [$end => ['正しい日付を入力してください。']],
            ];
            yield "when the {$end} does not exist" => [
                function (array &$input) use ($code): void {
                    $input['dwsPeriods'][$code->value()]['end'] = '1999-02-29';
                },
                [$end => ['正しい日付を入力してください。']],
            ];
            yield "when the {$end} equals the {$start}" => [
                function (array &$input) use ($code): void {
                    $input['dwsPeriods'][$code->value()]['start'] = '2020-05-17';
                    $input['dwsPeriods'][$code->value()]['end'] = '2020-05-17';
                },
                [$end => ['初回サービス提供日以降の日付を入力してください。']],
            ];
            yield "when the {$end} is before the {$start}" => [
                function (array &$input) use ($code): void {
                    $input['dwsPeriods'][$code->value()]['start'] = '2020-05-17';
                    $input['dwsPeriods'][$code->value()]['end'] = '2020-05-16';
                },
                [$end => ['初回サービス提供日以降の日付を入力してください。']],
            ];
        }

        // 備考
        yield 'when the note is longer than 255 characters' => [
            function (array &$input): void {
                $input['note'] = str_repeat('あ', 256);
            },
            ['note' => ['255文字以内で入力してください。']],
        ];
    }
}
