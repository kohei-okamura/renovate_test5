<?php
/**
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateOwnExpenseProgramRequest;
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
 * {@link \App\Http\Requests\UpdateOwnExpenseProgramRequest} のテスト.
 */
class UpdateOwnExpenseProgramRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use LookupOfficeUseCaseMixin;
    use MockeryMixin;
    use UnitSupport;

    protected UpdateOwnExpenseProgramRequest $request;
    private OwnExpenseProgram $ownExpenseProgram;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (UpdateOwnExpenseProgramRequestTest $self): void {
            $self->faker = app(Generator::class);
            $self->request = new UpdateOwnExpenseProgramRequest();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->with(anInstanceOf(Context::class), Permission::createOwnExpensePrograms(), self::NOT_EXISTING_ID)
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
        $this->should('return assoc of OwnExpenseProgram', function (): void {
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
            'return assoc of OwnExpenseProgram when non-required param is null or empty',
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
                    $this->assertEquals(
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
                $this->assertEquals(
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
            $this->assertTrue($validator->passes());
        });
        $examples = [
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
            'when note is over 255 letters' => [
                ['note' => ['255文字以内で入力してください。']],
                ['note' => $this->faker->numerify(str_repeat('#', 256))],
                ['note' => $this->ownExpenseProgram->note],
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $input = $this->defaultInput();
                foreach ($invalid as $key => $value) {
                    Arr::set($input, $key, $value);
                }
                $validator = $this->request->createValidatorInstance($invalid + $input);
                $this->assertTrue($validator->fails());
                $this->assertEquals($expected, $validator->errors()->toArray());
                if ($valid !== null) {
                    foreach ($valid as $key => $value) {
                        Arr::set($input, $key, $value);
                    }
                    $validator = $this->request->createValidatorInstance($valid + $input);
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
            'name' => $this->ownExpenseProgram->name,
            'note' => $this->ownExpenseProgram->note,
        ];
    }

    /**
     * payload が返す連想配列.
     *
     * @param array $input
     * @return array
     */
    private function expectedPayload(array $input): array
    {
        return [
            'name' => $input['name'],
            'note' => $input['note'] ?? '',
        ];
    }
}
