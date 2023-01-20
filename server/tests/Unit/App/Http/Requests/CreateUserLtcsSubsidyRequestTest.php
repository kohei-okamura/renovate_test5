<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\CreateUserLtcsSubsidyRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Common\CarbonRange;
use Domain\Common\DefrayerCategory;
use Domain\User\UserLtcsSubsidy;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Lib\Json;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Test;

/**
 * CreateUserLtcsSubsidyRequest のテスト.
 */
class CreateUserLtcsSubsidyRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use UnitSupport;

    protected CreateUserLtcsSubsidyRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateUserLtcsSubsidyRequestTest $self): void {
            $self->request = new CreateUserLtcsSubsidyRequest();
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
        $this->should('payload return Subsidy', function (): void {
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
                $this->expectedPayload(),
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
            $this->assertTrue($validator->passes());
        });
        $examples = [
            'when period.start is empty' => [
                ['period.start' => ['入力してください。']],
                ['period.start' => ''],
                ['period.start' => $this->examples->userLtcsSubsidies[0]->period->start],
            ],
            'when period.start is not date' => [
                ['period.start' => ['正しい日付を入力してください。']],
                ['period.start' => 'date'],
                ['period.start' => $this->examples->userLtcsSubsidies[0]->period->start],
            ],
            'when period.end is empty' => [
                ['period.end' => ['入力してください。']],
                ['period.end' => ''],
                ['period.end' => $this->examples->userLtcsSubsidies[0]->period->end],
            ],
            'when period.end is not date' => [
                ['period.end' => ['正しい日付を入力してください。']],
                ['period.end' => 'date'],
                ['period.end' => $this->examples->userLtcsSubsidies[0]->period->end],
            ],
            'when defrayerCategory is empty' => [
                ['defrayerCategory' => ['入力してください。']],
                ['defrayerCategory' => ''],
                ['defrayerCategory' => $this->examples->userLtcsSubsidies[0]->defrayerCategory->value()],
            ],
            'when defrayerCategory is invalid code' => [
                ['defrayerCategory' => ['公費制度（法別番号）を指定してください。']],
                ['defrayerCategory' => 0],
                ['defrayerCategory' => $this->examples->userLtcsSubsidies[0]->defrayerCategory->value()],
            ],
            'when defrayerNumber is empty' => [
                ['defrayerNumber' => ['入力してください。']],
                ['defrayerNumber' => ''],
                ['defrayerNumber' => $this->examples->userLtcsSubsidies[0]->defrayerNumber],
            ],
            'when defrayerNumber is not string' => [
                ['defrayerNumber' => ['文字列で入力してください。']],
                ['defrayerNumber' => 1],
                ['defrayerNumber' => $this->examples->userLtcsSubsidies[0]->defrayerNumber],
            ],
            'when defrayerNumber is over 8 letters' => [
                ['defrayerNumber' => ['8文字以内で入力してください。']],
                ['defrayerNumber' => Str::random(9)],
                ['defrayerNumber' => Str::random(8)],
            ],
            'when recipientNumber is empty' => [
                ['recipientNumber' => ['入力してください。']],
                ['recipientNumber' => ''],
                ['recipientNumber' => $this->examples->userLtcsSubsidies[0]->recipientNumber],
            ],
            'when recipientNumber is not string' => [
                ['recipientNumber' => ['文字列で入力してください。']],
                ['recipientNumber' => 1],
                ['recipientNumber' => $this->examples->userLtcsSubsidies[0]->recipientNumber],
            ],
            'when recipientNumber is over 7 letters' => [
                ['recipientNumber' => ['7文字以内で入力してください。']],
                ['recipientNumber' => Str::random(8)],
                ['recipientNumber' => Str::random(7)],
            ],
            'when benefitRate is empty' => [
                ['benefitRate' => ['入力してください。']],
                ['benefitRate' => ''],
                ['benefitRate' => $this->examples->userLtcsSubsidies[0]->benefitRate],
            ],
            'when benefitRate is not integer' => [
                ['benefitRate' => ['整数で入力してください。']],
                ['benefitRate' => 'A'],
                ['benefitRate' => $this->examples->userLtcsSubsidies[0]->benefitRate],
            ],
            'when benefitRate is under 1' => [
                ['benefitRate' => ['1〜100の範囲内で入力してください。']],
                ['benefitRate' => 0],
                ['benefitRate' => 1],
            ],
            'when benefitRate is over 100' => [
                ['benefitRate' => ['1〜100の範囲内で入力してください。']],
                ['benefitRate' => 101],
                ['benefitRate' => 100],
            ],
            'when copay is empty' => [
                ['copay' => ['入力してください。']],
                ['copay' => ''],
                ['copay' => $this->examples->userLtcsSubsidies[0]->copay],
            ],
            'when copay is negative number' => [
                ['copay' => ['0以上で入力してください。']],
                ['copay' => -1],
                ['copay' => 0],
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
        $userLtcsSubsidy = $this->examples->userLtcsSubsidies[0];
        return [
            'period' => [
                'start' => $userLtcsSubsidy->period->start,
                'end' => $userLtcsSubsidy->period->end,
            ],
            'defrayerCategory' => $userLtcsSubsidy->defrayerCategory->value(),
            'defrayerNumber' => $userLtcsSubsidy->defrayerNumber,
            'recipientNumber' => $userLtcsSubsidy->recipientNumber,
            'benefitRate' => $userLtcsSubsidy->benefitRate,
            'copay' => $userLtcsSubsidy->copay,
        ];
    }

    /**
     * payload が返すドメインモデル
     *
     * @return \Domain\User\UserLtcsSubsidy
     */
    private function expectedPayload(): UserLtcsSubsidy
    {
        $input = $this->defaultInput();
        $value = [
            'period' => CarbonRange::create($input['period']),
            'defrayerCategory' => DefrayerCategory::from($input['defrayerCategory']),
            'defrayerNumber' => $input['defrayerNumber'],
            'recipientNumber' => $input['recipientNumber'],
            'benefitRate' => $input['benefitRate'],
            'copay' => $input['copay'],
            'isEnabled' => true,
        ];
        return UserLtcsSubsidy::create($value);
    }
}
