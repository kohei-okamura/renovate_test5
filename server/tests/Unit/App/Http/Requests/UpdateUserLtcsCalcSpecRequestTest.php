<?php
/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\UpdateUserLtcsCalcSpecRequest;
use Domain\Common\Carbon;
use Domain\User\LtcsUserLocationAddition;
use Domain\User\UserLtcsCalcSpec;
use Illuminate\Support\Arr;
use Laravel\Lumen\Routing\Closure;
use Lib\Json;
use ScalikePHP\Seq;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\UpdateUserLtcsCalcSpecRequest} のテスト.
 */
final class UpdateUserLtcsCalcSpecRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use MockeryMixin;
    use UnitSupport;

    private UserLtcsCalcSpec $userLtcsCalcSpec;
    private UpdateUserLtcsCalcSpecRequest $request;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
            $self->userLtcsCalcSpec = $self->examples->userLtcsCalcSpecs[0];
        });
        self::beforeEachSpec(function (self $self): void {
            $self->request = new UpdateUserLtcsCalcSpecRequest();
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
            StaffRequest::prepareStaffRequest(
                $self->request,
                $self->examples->staffs[0],
                Seq::fromArray($self->examples->roles),
                Seq::fromArray($self->examples->offices),
                Seq::empty(),
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_payload(): void
    {
        $this->should('return assoc of UserLtcsCalcSpec', function (): void {
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
            'when name is empty' => [
                ['effectivatedOn' => ['入力してください。']],
                ['effectivatedOn' => ''],
                ['effectivatedOn' => $this->userLtcsCalcSpec->effectivatedOn],
            ],
            'when effectivatedOn is not date' => [
                ['effectivatedOn' => ['正しい日付を入力してください。']],
                ['effectivatedOn' => 'date'],
                ['effectivatedOn' => $this->userLtcsCalcSpec->effectivatedOn],
            ],
            'when locationAddition is empty' => [
                ['locationAddition' => ['入力してください。']],
                ['locationAddition' => ''],
                ['locationAddition' => $this->userLtcsCalcSpec->locationAddition->value()],
            ],
            'when unknown locationAddition given' => [
                ['locationAddition' => ['利用者別地域加算区分（介保）を指定してください。']],
                ['locationAddition' => self::INVALID_ENUM_VALUE],
                ['locationAddition' => $this->userLtcsCalcSpec->locationAddition->value()],
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
            'effectivatedOn' => $this->userLtcsCalcSpec->effectivatedOn->format('Y-m-d'),
            'locationAddition' => $this->userLtcsCalcSpec->locationAddition->value(),
        ];
    }

    /**
     * 入力値を生成する.
     *
     * @param null|\Closure $f
     * @return array
     */
    private function updateInput(?Closure $f = null): array
    {
        $input = [
            'effectivatedOn' => Carbon::create(2020),
            'locationAddition' => LtcsUserLocationAddition::none(),

            // URLパラメータ
            'userId' => $this->examples->users[0]->id,
        ];
        if ($f !== null) {
            $f($input);
        }
        return $input;
    }

    /**
     * payload が返す配列.
     *
     * @param array $input
     * @return array
     */
    private function expectedPayload(array $input): array
    {
        return [
            'effectivatedOn' => Carbon::parse($input['effectivatedOn']),
            'locationAddition' => LtcsUserLocationAddition::from($input['locationAddition']),
        ];
    }
}
