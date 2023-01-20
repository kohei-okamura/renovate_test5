<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\CreateUserDwsSubsidyRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Common\Carbon;
use Domain\Common\CarbonRange;
use Domain\Common\Rounding;
use Domain\User\UserDwsSubsidy;
use Domain\User\UserDwsSubsidyFactor;
use Domain\User\UserDwsSubsidyType;
use Faker\Generator;
use Illuminate\Support\Arr;
use Lib\Json;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Test;

/**
 * CreateUserDwsSubsidyRequest のテスト.
 */
class CreateUserDwsSubsidyRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use UnitSupport;

    private CreateUserDwsSubsidyRequest $request;
    private Generator $faker;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateUserDwsSubsidyRequestTest $self): void {
            $self->faker = app(Generator::class);
            $self->request = new CreateUserDwsSubsidyRequest();
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
        $this->should('payload return UserDwsSubsidy', function (): void {
            // リクエスト内容を反映させるために initialize() を利用する
            $this->request->initialize(
                [],
                [],
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                Json::encode($this->defaultInput())
            );
            $this->assertEquals(
                $this->expectedPayload($this->defaultInput()),
                $this->request->payload()
            );
        });
        $this->should(
            'return array when non-required param is null or empty',
            function ($key): void {
                foreach (['', null] as $value) {
                    $input = $this->defaultInput();
                    Arr::set($input, $key, $value);
                    // リクエスト内容を反映させるために initialize() を利用する
                    $this->request->initialize(
                        [],
                        [],
                        [],
                        [],
                        [],
                        ['CONTENT_TYPE' => 'application/json'],
                        Json::encode($input)
                    );
                    $this->assertModelStrictEquals(
                        $this->expectedPayload($input),
                        $this->request->payload()
                    );
                }
            },
            [
                'examples' => [
                    'when note' => [
                        'note',
                    ],
                ],
            ]
        );
        $this->should('return DwsCertification when non-required param is omitted', function ($key): void {
            $input = $this->defaultInput();
            Arr::forget($input, $key);
            // リクエスト内容を反映させるために initialize() を利用する
            $this->request->initialize(
                [],
                [],
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                Json::encode($input)
            );
            $this->assertModelStrictEquals(
                $this->expectedPayload($input),
                $this->request->payload()
            );
        }, [
            'examples' => [
                'when note' => [
                    'note',
                ],
            ],
        ]);
        $this->should('return UserDwsSubsidy with factor when subsidyType is benefitRate', function (): void {
            $input = tap($this->defaultInput(), function (array &$assoc): void {
                $assoc['subsidyType'] = UserDwsSubsidyType::benefitRate()->value();
            });
            // リクエスト内容を反映させるために initialize() を利用する
            $this->request->initialize(
                [],
                [],
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                Json::encode($input)
            );
            $this->assertSame(UserDwsSubsidyFactor::from($input['factor']), $this->request->payload()->factor);
        });
        $this->should('return UserDwsSubsidy with factor when subsidyType is copayRate', function (): void {
            $input = tap($this->defaultInput(), function (array &$assoc): void {
                $assoc['subsidyType'] = UserDwsSubsidyType::copayRate()->value();
            });
            // リクエスト内容を反映させるために initialize() を利用する
            $this->request->initialize(
                [],
                [],
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                Json::encode($input)
            );
            $this->assertSame(UserDwsSubsidyFactor::from($input['factor']), $this->request->payload()->factor);
        });
        $this->should('return UserDwsSubsidy with factor none when subsidyType is neither benefitRate nor copayRate', function (): void {
            $input = tap($this->defaultInput(), function (array &$assoc): void {
                $assoc['subsidyType'] = UserDwsSubsidyType::benefitAmount()->value();
            });
            // リクエスト内容を反映させるために initialize() を利用する
            $this->request->initialize(
                [],
                [],
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                Json::encode($input)
            );
            $this->assertSame(UserDwsSubsidyFactor::none(), $this->request->payload()->factor);
        });
        $this->should('return UserDwsSubsidy with benefitRate when subsidyType is benefitRate', function (): void {
            $input = tap($this->defaultInput(), function (array &$assoc): void {
                $assoc['subsidyType'] = UserDwsSubsidyType::benefitRate()->value();
            });
            // リクエスト内容を反映させるために initialize() を利用する
            $this->request->initialize(
                [],
                [],
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                Json::encode($input)
            );
            $this->assertSame($input['benefitRate'], $this->request->payload()->benefitRate);
        });
        $this->should('return UserDwsSubsidy with benefitRate 0 when subsidyType is not benefitRate', function (): void {
            $input = tap($this->defaultInput(), function (array &$assoc): void {
                $assoc['subsidyType'] = UserDwsSubsidyType::copayAmount()->value();
            });
            // リクエスト内容を反映させるために initialize() を利用する
            $this->request->initialize(
                [],
                [],
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                Json::encode($input)
            );
            $this->assertSame(0, $this->request->payload()->benefitRate);
        });
        $this->should('return UserDwsSubsidy with copayRate when subsidyType is copayRate', function (): void {
            $input = tap($this->defaultInput(), function (array &$assoc): void {
                $assoc['subsidyType'] = UserDwsSubsidyType::copayRate()->value();
            });
            // リクエスト内容を反映させるために initialize() を利用する
            $this->request->initialize(
                [],
                [],
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                Json::encode($input)
            );
            $this->assertSame($input['copayRate'], $this->request->payload()->copayRate);
        });
        $this->should('return UserDwsSubsidy with copayRate 0 when subsidyType is not copayRate', function (): void {
            $input = tap($this->defaultInput(), function (array &$assoc): void {
                $assoc['subsidyType'] = UserDwsSubsidyType::benefitAmount()->value();
            });
            // リクエスト内容を反映させるために initialize() を利用する
            $this->request->initialize(
                [],
                [],
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                Json::encode($input)
            );
            $this->assertSame(0, $this->request->payload()->copayRate);
        });
        $this->should('return UserDwsSubsidy with benefitAmount when subsidyType is benefitAmount', function (): void {
            $input = tap($this->defaultInput(), function (array &$assoc): void {
                $assoc['subsidyType'] = UserDwsSubsidyType::benefitAmount()->value();
            });
            // リクエスト内容を反映させるために initialize() を利用する
            $this->request->initialize(
                [],
                [],
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                Json::encode($input)
            );
            $this->assertSame($input['benefitAmount'], $this->request->payload()->benefitAmount);
        });
        $this->should('return UserDwsSubsidy with benefitAmount 0 when subsidyType is not benefitAmount', function (): void {
            $input = tap($this->defaultInput(), function (array &$assoc): void {
                $assoc['subsidyType'] = UserDwsSubsidyType::benefitRate()->value();
            });
            // リクエスト内容を反映させるために initialize() を利用する
            $this->request->initialize(
                [],
                [],
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                Json::encode($input)
            );
            $this->assertSame(0, $this->request->payload()->benefitAmount);
        });
        $this->should('return UserDwsSubsidy with copayAmount when subsidyType is copayAmount', function (): void {
            $input = tap($this->defaultInput(), function (array &$assoc): void {
                $assoc['subsidyType'] = UserDwsSubsidyType::copayAmount()->value();
            });
            // リクエスト内容を反映させるために initialize() を利用する
            $this->request->initialize(
                [],
                [],
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                Json::encode($input)
            );
            $this->assertSame($input['copayAmount'], $this->request->payload()->copayAmount);
        });
        $this->should('return UserDwsSubsidy with copayAmount 0 when subsidyType is not copayAmount', function (): void {
            $input = tap($this->defaultInput(), function (array &$assoc): void {
                $assoc['subsidyType'] = UserDwsSubsidyType::copayRate()->value();
            });
            // リクエスト内容を反映させるために initialize() を利用する
            $this->request->initialize(
                [],
                [],
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                Json::encode($input)
            );
            $this->assertSame(0, $this->request->payload()->copayAmount);
        });
        $this->should('return UserDwsSubsidy with rounding when subsidyType is benefitRate', function (): void {
            $input = tap($this->defaultInput(), function (array &$assoc): void {
                $assoc['subsidyType'] = UserDwsSubsidyType::benefitRate()->value();
            });
            // リクエスト内容を反映させるために initialize() を利用する
            $this->request->initialize(
                [],
                [],
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                Json::encode($input)
            );
            $this->assertSame(Rounding::from($input['rounding']), $this->request->payload()->rounding);
        });
        $this->should('return UserDwsSubsidy with rounding when subsidyType is copayRate', function (): void {
            $input = tap($this->defaultInput(), function (array &$assoc): void {
                $assoc['subsidyType'] = UserDwsSubsidyType::copayRate()->value();
            });
            // リクエスト内容を反映させるために initialize() を利用する
            $this->request->initialize(
                [],
                [],
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                Json::encode($input)
            );
            $this->assertSame(Rounding::from($input['rounding']), $this->request->payload()->rounding);
        });
        $this->should('return UserDwsSubsidy with rounding none when subsidyType is neither benefitRate nor copayRate', function (): void {
            $input = tap($this->defaultInput(), function (array &$assoc): void {
                $assoc['subsidyType'] = UserDwsSubsidyType::benefitAmount()->value();
            });
            // リクエスト内容を反映させるために initialize() を利用する
            $this->request->initialize(
                [],
                [],
                [],
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                Json::encode($input)
            );
            $this->assertSame(Rounding::none(), $this->request->payload()->rounding);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validation(): void
    {
        $this->should('succeed when the data passes the validation rules', function (): void {
            $validator = $this->request->createValidatorInstance($this->defaultInput());
            $this->assertTrue($validator->passes());
        });
        $examples = [
            'when period.start is empty' => [
                ['period.start' => ['入力してください。']],
                ['period.start' => ''],
                ['period.start' => $this->examples->userDwsSubsidies[0]->period->start],
            ],
            'when period.start is not date' => [
                ['period.start' => ['正しい日付を入力してください。']],
                ['period.start' => 'date'],
                ['period.start' => $this->examples->userDwsSubsidies[0]->period->start],
            ],
            'when period.end is empty' => [
                ['period.end' => ['入力してください。']],
                ['period.end' => ''],
                ['period.end' => $this->examples->userDwsSubsidies[0]->period->end],
            ],
            'when period.end is not date' => [
                ['period.end' => ['正しい日付を入力してください。']],
                ['period.end' => 'date'],
                ['period.end' => $this->examples->userDwsSubsidies[0]->period->end],
            ],
            'when cityName is empty' => [
                ['cityName' => ['入力してください。']],
                ['cityName' => ''],
                ['cityName' => $this->examples->userDwsSubsidies[0]->cityName],
            ],
            'when cityName is not string' => [
                ['cityName' => ['文字列で入力してください。']],
                ['cityName' => 1],
                ['cityName' => $this->examples->userDwsSubsidies[0]->cityName],
            ],
            'when cityName is over 100 letters' => [
                ['cityName' => ['100文字以内で入力してください。']],
                ['cityName' => $this->faker->numerify(str_repeat('#', 101))],
                ['cityName' => 'aaa'],
            ],
            'when cityCode is empty' => [
                ['cityCode' => ['入力してください。']],
                ['cityCode' => ''],
                ['cityCode' => $this->examples->userDwsSubsidies[0]->cityCode],
            ],
            'when cityCode is not string' => [
                ['cityCode' => ['文字列で入力してください。']],
                ['cityCode' => 1],
                ['cityCode' => $this->examples->userDwsSubsidies[0]->cityCode],
            ],
            'when cityCode is over 6 letters' => [
                ['cityCode' => ['6文字以内で入力してください。']],
                ['cityCode' => 'aaaaaaa'],
                ['cityCode' => 'aaa'],
            ],
            'when subsidyType is empty' => [
                ['subsidyType' => ['入力してください。']],
                ['subsidyType' => ''],
                ['subsidyType' => $this->examples->userDwsSubsidies[0]->subsidyType->value()],
            ],
            'when subsidyType is invalid code' => [
                ['subsidyType' => ['給付方式を指定してください。']],
                ['subsidyType' => 0],
                ['subsidyType' => $this->examples->userDwsSubsidies[0]->subsidyType->value()],
            ],
            'when factor is empty' => [
                ['factor' => ['入力してください。']],
                [
                    'factor' => '',
                    'subsidyType' => UserDwsSubsidyType::benefitRate()->value(),
                ],
                [
                    'factor' => $this->examples->userDwsSubsidies[0]->factor->value(),
                    'subsidyType' => UserDwsSubsidyType::benefitRate()->value(),
                ],
            ],
            'when factor is invalid' => [
                ['factor' => ['利用者：自治体助成情報：基準値種別を指定してください。']],
                [
                    'factor' => self::INVALID_ENUM_VALUE,
                    'subsidyType' => UserDwsSubsidyType::benefitRate()->value(),
                ],
                [
                    'factor' => $this->examples->userDwsSubsidies[0]->factor->value(),
                    'subsidyType' => UserDwsSubsidyType::benefitRate()->value(),
                ],
            ],
            'when benefitRate is empty' => [
                ['benefitRate' => ['入力してください。']],
                [
                    'benefitRate' => '',
                    'subsidyType' => UserDwsSubsidyType::benefitRate()->value(),
                ],
                [
                    'benefitRate' => $this->examples->userDwsSubsidies[0]->benefitRate,
                    'subsidyType' => UserDwsSubsidyType::benefitRate()->value(),
                ],
            ],
            'when benefitRate is not integer' => [
                ['benefitRate' => ['整数で入力してください。']],
                [
                    'benefitRate' => 'A',
                    'subsidyType' => UserDwsSubsidyType::benefitRate()->value(),
                ],
                [
                    'benefitRate' => $this->examples->userDwsSubsidies[0]->benefitRate,
                    'subsidyType' => UserDwsSubsidyType::benefitRate()->value(),
                ],
            ],
            'when copayRate is empty' => [
                ['copayRate' => ['入力してください。']],
                [
                    'copayRate' => '',
                    'subsidyType' => UserDwsSubsidyType::copayRate()->value(),
                ],
                [
                    'copayRate' => $this->examples->userDwsSubsidies[0]->copayRate,
                    'subsidyType' => UserDwsSubsidyType::copayRate()->value(),
                ],
            ],
            'when copayRate is not integer' => [
                ['copayRate' => ['整数で入力してください。']],
                [
                    'copayRate' => 'A',
                    'subsidyType' => UserDwsSubsidyType::copayRate()->value(),
                ],
                [
                    'copayRate' => $this->examples->userDwsSubsidies[0]->copayRate,
                    'subsidyType' => UserDwsSubsidyType::copayRate()->value(),
                ],
            ],
            'when rounding is empty' => [
                ['rounding' => ['入力してください。']],
                [
                    'rounding' => '',
                    'subsidyType' => UserDwsSubsidyType::benefitRate()->value(),
                ],
                [
                    'rounding' => $this->examples->userDwsSubsidies[0]->rounding->value(),
                    'subsidyType' => UserDwsSubsidyType::benefitRate()->value(),
                ],
            ],
            'when rounding is invalid' => [
                ['rounding' => ['端数処理区分を指定してください。']],
                [
                    'rounding' => self::INVALID_ENUM_VALUE,
                    'subsidyType' => UserDwsSubsidyType::benefitRate()->value(),
                ],
                [
                    'rounding' => $this->examples->userDwsSubsidies[0]->rounding->value(),
                    'subsidyType' => UserDwsSubsidyType::benefitRate()->value(),
                ],
            ],
            'when benefitAmount is empty' => [
                ['benefitAmount' => ['入力してください。']],
                [
                    'benefitAmount' => '',
                    'subsidyType' => UserDwsSubsidyType::benefitAmount()->value(),
                ],
                [
                    'benefitAmount' => $this->examples->userDwsSubsidies[0]->benefitAmount,
                    'subsidyType' => UserDwsSubsidyType::benefitAmount()->value(),
                ],
            ],
            'when benefitAmount is not integer' => [
                ['benefitAmount' => ['整数で入力してください。']],
                [
                    'benefitAmount' => 'A',
                    'subsidyType' => UserDwsSubsidyType::benefitAmount()->value(),
                ],
                [
                    'benefitAmount' => $this->examples->userDwsSubsidies[0]->benefitAmount,
                    'subsidyType' => UserDwsSubsidyType::benefitAmount()->value(),
                ],
            ],
            'when copayAmount is empty' => [
                ['copayAmount' => ['入力してください。']],
                [
                    'copayAmount' => '',
                    'subsidyType' => UserDwsSubsidyType::copayAmount()->value(),
                ],
                [
                    'copayAmount' => $this->examples->userDwsSubsidies[0]->copayAmount,
                    'subsidyType' => UserDwsSubsidyType::copayAmount()->value(),
                ],
            ],
            'when copayAmount is not integer' => [
                ['copayAmount' => ['整数で入力してください。']],
                [
                    'copayAmount' => 'A',
                    'subsidyType' => UserDwsSubsidyType::copayAmount()->value(),
                ],
                [
                    'copayAmount' => $this->examples->userDwsSubsidies[0]->copayAmount,
                    'subsidyType' => UserDwsSubsidyType::copayAmount()->value(),
                ],
            ],
            'when note is over 255 letters' => [
                ['note' => ['255文字以内で入力してください。']],
                ['note' => $this->faker->numerify(str_repeat('#', 256))],
                ['note' => $this->examples->userDwsSubsidies[0]->note],
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $input = $this->defaultInput();
                foreach ($invalid as $key => $value) {
                    Arr::set($input, $key, $value);
                }
                $validator = $this->request->createValidatorInstance($input);
                $this->assertTrue($validator->fails());
                $this->assertSame($expected, $validator->errors()->toArray());
                if ($valid !== null) {
                    $validator = $this->request->createValidatorInstance($valid + $this->defaultInput());
                    $this->assertTrue($validator->passes());
                }
            },
            compact('examples')
        );
    }

    /**
     * リクエストクラスが受け取る入力のデフォルト値.
     *
     * @return array
     */
    private function defaultInput(): array
    {
        $userDwsSubsidy = $this->examples->userDwsSubsidies[0];
        return [
            'period' => [
                'start' => $userDwsSubsidy->period->start,
                'end' => $userDwsSubsidy->period->end,
            ],
            'cityName' => $userDwsSubsidy->cityName,
            'cityCode' => $userDwsSubsidy->cityCode,
            'subsidyType' => UserDwsSubsidyType::benefitRate()->value(),
            'factor' => UserDwsSubsidyFactor::copay()->value(),
            'benefitRate' => $userDwsSubsidy->benefitRate,
            'copayRate' => $userDwsSubsidy->copayRate,
            'rounding' => Rounding::floor()->value(),
            'benefitAmount' => $userDwsSubsidy->benefitAmount,
            'copayAmount' => $userDwsSubsidy->copayAmount,
            'note' => $userDwsSubsidy->note,
        ];
    }

    /**
     * payload が返すドメインモデル
     *
     * @param array $input
     * @return \Domain\User\UserDwsSubsidy
     */
    private function expectedPayload(array $input): UserDwsSubsidy
    {
        $value = [
            'period' => CarbonRange::create([
                'start' => Carbon::parse($input['period']['start']),
                'end' => Carbon::parse($input['period']['end']),
            ]),
            'cityName' => $input['cityName'],
            'cityCode' => $input['cityCode'],
            'subsidyType' => UserDwsSubsidyType::from($input['subsidyType']),
            'factor' => UserDwsSubsidyFactor::from($input['factor']),
            'benefitRate' => $input['benefitRate'],
            'copayRate' => $input['copayRate'],
            'rounding' => Rounding::from($input['rounding']),
            'benefitAmount' => $input['benefitAmount'],
            'copayAmount' => $input['copayAmount'],
            'note' => $input['note'] ?? '',
        ];
        return UserDwsSubsidy::create($value);
    }
}
