<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\CreateOwnExpenseProgramRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Common\Expense;
use Domain\Common\TaxCategory;
use Domain\Common\TaxType;
use Domain\Context\Context;
use Domain\OwnExpenseProgram\OwnExpenseProgram;
use Domain\Permission\Permission;
use Faker\Generator;
use Illuminate\Support\Arr;
use Lib\Json;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\CreateOwnExpenseProgramRequest} のテスト.
 */
class CreateOwnExpenseProgramRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupOfficeUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;
    protected CreateOwnExpenseProgramRequest $request;

    private Generator $faker;
    private OwnExpenseProgram $ownExpenseProgram;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateOwnExpenseProgramRequestTest $self): void {
            $self->faker = app(Generator::class);
            $self->request = new CreateOwnExpenseProgramRequest();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), [Permission::createOwnExpensePrograms()], self::NOT_EXISTING_ID)
                ->andReturn(Seq::emptySeq())
                ->byDefault();

            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::emptySeq(),
            );
            $self->ownExpenseProgram = $self->examples->ownExpensePrograms[0];
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_payload(): void
    {
        $this->should('payload return OwnExpenseProgram', function (): void {
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
            $this->assertModelStrictEquals(
                $this->expectedPayload($this->defaultInput()),
                $this->request->payload()
            );
        });
        $this->should(
            'return OwnExpenseProgram when non-required param is null or empty',
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
        $this->should(
            'return OwnExpenseProgram when non-required param is undefined',
            function ($key): void {
                $forgetInput = $this->defaultInput();
                Arr::forget($forgetInput, $key);
                // リクエスト内容を反映させるために initialize() を利用する
                $this->request->initialize(
                    [],
                    [],
                    [],
                    [],
                    [],
                    ['CONTENT_TYPE' => 'application/json'],
                    Json::encode($forgetInput)
                );
                $this->assertModelStrictEquals(
                    $this->expectedPayload($forgetInput),
                    $this->request->payload()
                );
            },
            [
                'examples' => [
                    'when officeId' => [
                        'officeId',
                    ],
                    'when note' => [
                        'note',
                    ],
                ],
            ]
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_validation(): void
    {
        $this->should('succeed when the data passes the validation rules', function (): void {
            $validator = $this->request->createValidatorInstance($this->defaultInput());
            $this->assertTrue($validator->passes(), $validator->errors()->toJson());
        });
        $examples = [
            'when unknown officeId given' => [
                ['officeId' => ['正しい値を入力してください。']],
                ['officeId' => self::NOT_EXISTING_ID],
                ['officeId' => $this->ownExpenseProgram->officeId],
            ],
            'when name is empty' => [
                ['name' => ['入力してください。']],
                ['name' => ''],
                ['name' => $this->ownExpenseProgram->name],
            ],
            'when name is not string' => [
                ['name' => ['文字列で入力してください。']],
                ['name' => 1],
                ['name' => $this->ownExpenseProgram->name],
            ],
            'when name is over 200 letters' => [
                ['name' => ['200文字以内で入力してください。']],
                ['name' => $this->faker->numerify(str_repeat('#', 201))],
                ['name' => $this->ownExpenseProgram->name],
            ],
            'when durationMinutes is empty' => [
                ['durationMinutes' => ['入力してください。']],
                ['durationMinutes' => ''],
                ['durationMinutes' => $this->ownExpenseProgram->durationMinutes],
            ],
            'when durationMinutes is not integer' => [
                ['durationMinutes' => ['整数で入力してください。']],
                ['durationMinutes' => 'error'],
                ['durationMinutes' => $this->ownExpenseProgram->durationMinutes],
            ],
            'when taxExcluded is empty' => [
                ['fee.taxExcluded' => ['入力してください。']],
                ['fee.taxExcluded' => ''],
                ['fee.taxExcluded' => $this->ownExpenseProgram->fee->taxExcluded],
            ],
            'when taxExcluded is not integer' => [
                ['fee.taxExcluded' => ['整数で入力してください。']],
                ['fee.taxExcluded' => 'error'],
                ['fee.taxExcluded' => $this->ownExpenseProgram->fee->taxExcluded],
            ],
            'when taxIncluded is empty' => [
                ['fee.taxIncluded' => ['入力してください。']],
                ['fee.taxIncluded' => ''],
                ['fee.taxIncluded' => $this->ownExpenseProgram->fee->taxIncluded],
            ],
            'when taxIncluded is not integer' => [
                ['fee.taxIncluded' => ['整数で入力してください。']],
                ['fee.taxIncluded' => 'error'],
                ['fee.taxIncluded' => $this->ownExpenseProgram->fee->taxIncluded],
            ],
            'when taxType is empty' => [
                ['fee.taxType' => ['入力してください。']],
                ['fee.taxType' => ''],
                ['fee.taxType' => $this->ownExpenseProgram->fee->taxType->value()],
            ],
            'when unknown taxType given' => [
                ['fee.taxType' => ['課税区分を選択してください。']],
                ['fee.taxType' => self::INVALID_ENUM_VALUE],
                ['fee.taxType' => $this->ownExpenseProgram->fee->taxType->value()],
            ],
            'when taxCategory is empty' => [
                ['fee.taxCategory' => ['入力してください。']],
                ['fee.taxCategory' => ''],
                ['fee.taxCategory' => $this->ownExpenseProgram->fee->taxCategory->value()],
            ],
            'when unknown taxCategory given' => [
                ['fee.taxCategory' => ['税率区分を選択してください。']],
                ['fee.taxCategory' => self::INVALID_ENUM_VALUE],
                ['fee.taxCategory' => $this->ownExpenseProgram->fee->taxCategory->value()],
            ],
            'when note is over 255 letters' => [
                ['note' => ['255文字以内で入力してください。']],
                ['note' => $this->faker->numerify(str_repeat('#', 256))],
                ['note' => $this->ownExpenseProgram->note],
            ],
            'when taxExcluded is invalid price' => [
                ['fee.taxExcluded' => ['税抜金額が正しくありません。']],
                ['fee.taxExcluded' => 1200],
                ['fee.taxExcluded' => 1000],
            ],
            'when taxIncluded is invalid price' => [
                ['fee.taxIncluded' => ['税込金額が正しくありません。']],
                [
                    'fee.taxType' => TaxType::taxExcluded()->value(),
                    'fee.taxCategory' => TaxCategory::reducedConsumptionTax()->value(),
                ],
                [
                    'fee.taxType' => TaxType::taxExcluded()->value(),
                    'fee.taxCategory' => TaxCategory::consumptionTax()->value(),
                ],
            ],
            'when taxIncluded and taxExcluded are unmatched with TaxType::taxExempted' => [
                [
                    'fee.taxExcluded' => ['税抜金額が正しくありません。'],
                    'fee.taxIncluded' => ['税込金額が正しくありません。'],
                ],
                ['fee.taxType' => TaxType::taxExempted()->value()],
                [
                    'fee.taxType' => TaxType::taxExempted()->value(),
                    'fee.taxExcluded' => 1100,
                ],
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
                $this->assertEquals($expected, $validator->errors()->toArray());
                if ($valid !== null) {
                    foreach ($valid as $key => $value) {
                        Arr::set($input, $key, $value);
                    }
                    $validator = $this->request->createValidatorInstance($input);
                    $this->assertTrue($validator->passes(), $validator->errors()->toJson(\JSON_UNESCAPED_UNICODE));
                    $this->assertSame([], $validator->errors()->toArray());
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
        return [
            'officeId' => $this->ownExpenseProgram->officeId,
            'name' => $this->ownExpenseProgram->name,
            'durationMinutes' => $this->ownExpenseProgram->durationMinutes,
            'fee' => [
                'taxExcluded' => $this->ownExpenseProgram->fee->taxExcluded,
                'taxIncluded' => $this->ownExpenseProgram->fee->taxIncluded,
                'taxType' => $this->ownExpenseProgram->fee->taxType->value(),
                'taxCategory' => $this->ownExpenseProgram->fee->taxCategory->value(),
            ],
            'note' => $this->ownExpenseProgram->note,
        ];
    }

    /**
     * payload が返すドメインモデル.
     *
     * @param array $input
     * @return \Domain\OwnExpenseProgram\OwnExpenseProgram
     */
    private function expectedPayload(array $input): OwnExpenseProgram
    {
        return OwnExpenseProgram::create([
            'officeId' => $input['officeId'] ?? null,
            'name' => $input['name'],
            'durationMinutes' => $input['durationMinutes'],
            'fee' => Expense::create([
                'taxExcluded' => $input['fee']['taxExcluded'],
                'taxIncluded' => $input['fee']['taxIncluded'],
                'taxType' => TaxType::from($input['fee']['taxType']),
                'taxCategory' => TaxCategory::from($input['fee']['taxCategory']),
            ]),
            'note' => $input['note'] ?? '',
        ]);
    }
}
