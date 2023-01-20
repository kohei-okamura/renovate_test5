<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\FindLtcsHomeVisitLongTermCareDictionaryEntryRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Domain\Project\LtcsProjectServiceCategory;
use Domain\ServiceCodeDictionary\Timeframe;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Test;

/**
 * FindLtcsHomeVisitLongTermCareDictionaryEntryRequest のテスト.
 */
class FindLtcsHomeVisitLongTermCareDictionaryEntryRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ContextMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use OrganizationRepositoryMixin;
    use UnitSupport;

    public const PAGINATION_PARAMS = [];
    private FindLtcsHomeVisitLongTermCareDictionaryEntryRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (FindLtcsHomeVisitLongTermCareDictionaryEntryRequestTest $self): void {
            $self->organizationRepository
                ->allows('lookupOptionByCode')
                ->andReturn(Option::from($self->context->organization))
                ->byDefault();
            $self->request = (new FindLtcsHomeVisitLongTermCareDictionaryEntryRequest())->replace($self->input());
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
    public function describe_filterParams(): void
    {
        $this->should('return an array of specified filter params', function (): void {
            $this->assertEquals($this->input(), $this->request->filterParams());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_paginationParams(): void
    {
        $this->should('return an array of pagination params', function (): void {
            $this->assertSame(self::PAGINATION_PARAMS, $this->request->paginationParams());
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validation(): void
    {
        $this->should('succeed when the data passes the validation rules', function (): void {
            $validator = $this->request->createValidatorInstance($this->input());
            $this->assertTrue($validator->passes());
        });
        $examples = [
            'when officeId is empty' => [
                ['officeId' => ['入力してください。']],
                ['officeId' => ''],
                ['officeId' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->officeId],
            ],
            'when officeId is string' => [
                ['officeId' => ['整数で入力してください。']],
                ['officeId' => 'aaa'],
                ['officeId' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->officeId],
            ],
            'when isEffectiveOn is empty' => [
                ['isEffectiveOn' => ['入力してください。']],
                ['isEffectiveOn' => ''],
                ['isEffectiveOn' => $this->examples->ltcsHomeVisitLongTermCareDictionaries[0]->effectivatedOn],
            ],
            'when isEffectiveOn is not date' => [
                ['isEffectiveOn' => ['正しい日付を入力してください。']],
                ['isEffectiveOn' => 'aaa'],
                ['isEffectiveOn' => $this->examples->ltcsHomeVisitLongTermCareDictionaries[0]->effectivatedOn],
            ],
            'when q is not string' => [
                ['q' => ['文字列で入力してください。', 'サービスコードは6文字で入力してください。']],
                ['q' => 123],
                ['q' => $this->examples->ltcsHomeVisitLongTermCareDictionaryEntries[0]->serviceCode->toString()],
            ],
            'when q is not 6 letters' => [
                ['q' => ['サービスコードは6文字で入力してください。']],
                ['q' => 'aaa'],
                ['q' => $this->examples->ltcsHomeVisitLongTermCareDictionaryEntries[0]->serviceCode->toString()],
            ],
            'when timeframe is invalid value' => [
                ['timeframe' => ['時間帯を指定してください。']],
                ['timeframe' => 'aaa'],
                ['timeframe' => $this->examples->ltcsHomeVisitLongTermCareDictionaryEntries[0]->timeframe->value()],
            ],
            'when category is invalid value' => [
                ['category' => ['介護保険サービス：計画：サービス区分を指定してください。']],
                ['category' => 'aaa'],
                ['category' => LtcsProjectServiceCategory::physicalCare()->value()],
            ],
            'when physicalMinutes is not integer' => [
                ['physicalMinutes' => ['整数で入力してください。']],
                ['physicalMinutes' => 'aaa'],
                ['physicalMinutes' => $this->examples->ltcsHomeVisitLongTermCareDictionaryEntries[0]->physicalMinutes->start],
            ],
            'when physicalMinutes is not between 1 to 1440' => [
                ['physicalMinutes' => ['1〜1440の範囲内で入力してください。']],
                ['physicalMinutes' => 0],
                ['physicalMinutes' => $this->examples->ltcsHomeVisitLongTermCareDictionaryEntries[0]->physicalMinutes->start],
            ],
            'when houseworkMinutes is not integer' => [
                ['houseworkMinutes' => ['整数で入力してください。']],
                ['houseworkMinutes' => 'aaa'],
                ['houseworkMinutes' => $this->examples->ltcsHomeVisitLongTermCareDictionaryEntries[0]->houseworkMinutes->start],
            ],
            'when houseworkMinutes is not between 1 to 1440' => [
                ['houseworkMinutes' => ['1〜1440の範囲内で入力してください。']],
                ['houseworkMinutes' => 0],
                ['houseworkMinutes' => $this->examples->ltcsHomeVisitLongTermCareDictionaryEntries[0]->houseworkMinutes->start],
            ],
            'when headcount is not integer' => [
                ['headcount' => ['整数で入力してください。', '1〜2の範囲内で入力してください。']],
                ['headcount' => 'aaa'],
                ['headcount' => $this->examples->ltcsHomeVisitLongTermCareDictionaryEntries[0]->headcount],
            ],
            'when headcount is not between 1 to 2' => [
                ['headcount' => ['1〜2の範囲内で入力してください。']],
                ['headcount' => 0],
                ['headcount' => $this->examples->ltcsHomeVisitLongTermCareDictionaryEntries[0]->headcount],
            ],
        ];
        $this->should(
            'fails when the data does not pass the validation rules',
            function ($expected, $invalid, $valid = null): void {
                $validator = $this->request->createValidatorInstance($invalid + $this->input());
                $this->assertTrue($validator->fails());
                $this->assertSame($expected, $validator->errors()->toArray());
                if ($valid !== null) {
                    $validator = $this->request->createValidatorInstance($valid + $this->input());
                    $this->assertTrue($validator->passes(), $validator->errors()->toJson(\JSON_UNESCAPED_UNICODE));
                }
            },
            compact('examples')
        );
    }

    /**
     * リクエストクラスが受け取る入力値.
     *
     * @return array
     */
    private function input(): array
    {
        return [
            ...self::PAGINATION_PARAMS,
            ...$this->filterParams(),
            'timeframe' => (string)Timeframe::daytime()->value(),
            'category' => (string)LtcsProjectServiceCategory::physicalCare()->value(),
        ];
    }

    /**
     * フィルターパラメーターを返す.
     *
     * @return array
     */
    private function filterParams(): array
    {
        $entry = $this->examples->ltcsHomeVisitLongTermCareDictionaryEntries[0];
        return [
            'officeId' => $this->examples->homeVisitLongTermCareCalcSpecs[0]->officeId,
            'isEffectiveOn' => $this->examples->ltcsHomeVisitLongTermCareDictionaries[0]->effectivatedOn,
            'q' => $entry->serviceCode->toString(),
            'timeframe' => Timeframe::daytime(),
            'category' => LtcsProjectServiceCategory::housework(),
            'physicalMinutes' => $entry->physicalMinutes->start,
            'houseworkMinutes' => $entry->houseworkMinutes->start,
            'headcount' => $entry->headcount,
        ];
    }
}
