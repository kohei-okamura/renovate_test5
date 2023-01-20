<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Billing\LtcsHomeVisitLongTermCareDictionary;

use BillingTester;
use Codeception\Util\HttpCode;
use Domain\Common\IntRange;
use Domain\Office\HomeVisitLongTermCareSpecifiedOfficeAddition;
use Domain\ServiceCode\ServiceCode;
use Domain\ServiceCodeDictionary\LtcsCalcCycle;
use Domain\ServiceCodeDictionary\LtcsCalcExtraScore;
use Domain\ServiceCodeDictionary\LtcsCalcScore;
use Domain\ServiceCodeDictionary\LtcsCalcType;
use Domain\ServiceCodeDictionary\LtcsCompositionType;
use Domain\ServiceCodeDictionary\LtcsHomeVisitLongTermCareDictionaryEntry;
use Domain\ServiceCodeDictionary\LtcsNoteRequirement;
use Domain\ServiceCodeDictionary\LtcsServiceCodeCategory;
use Domain\ServiceCodeDictionary\Timeframe;
use Psr\Log\LogLevel;
use Tests\Billing\Test;
use Tests\Unit\Examples\ExamplesConsumer;

/**
 * GetLtcsHomeVisitLongTermCareDictionaryEntry テスト.
 */
class GetLtcsHomeVisitLongTermCareDictionaryEntryCest extends Test
{
    use ExamplesConsumer;

    // tests

    /**
     * API正常呼び出しテスト.
     *
     * @param BillingTester $I
     */
    public function succeedAPICall(BillingTester $I)
    {
        $I->wantTo('succeed API call');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $providedIn = '2021-11';

        $dictionaryEntry = LtcsHomeVisitLongTermCareDictionaryEntry::create([
            'serviceCode' => ServiceCode::fromString('111111'),
            'name' => '身体介護1',
            'category' => LtcsServiceCodeCategory::physicalCare(),
            'headcount' => 1,
            'compositionType' => LtcsCompositionType::basic(),
            'specifiedOfficeAddition' => HomeVisitLongTermCareSpecifiedOfficeAddition::none(),
            'noteRequirement' => LtcsNoteRequirement::none(),
            'isLimited' => true,
            'isBulkSubtractionTarget' => true,
            'isSymbioticSubtractionTarget' => true,
            'score' => LtcsCalcScore::create([
                'value' => 250,
                'calcType' => LtcsCalcType::score(),
                'calcCycle' => LtcsCalcCycle::perService(),
            ]),
            'extraScore' => LtcsCalcExtraScore::create([
                'isAvailable' => false,
                'baseMinutes' => 0,
                'unitScore' => 0,
                'unitMinutes' => 0,
                'specifiedOfficeAdditionCoefficient' => 0,
                'timeframeAdditionCoefficient' => 0,
            ]),
            'timeframe' => Timeframe::daytime(),
            'totalMinutes' => IntRange::create(['start' => 20, 'end' => 30]),
            'physicalMinutes' => IntRange::create(['start' => 20, 'end' => 30]),
            'houseworkMinutes' => IntRange::create(['start' => 0, 'end' => 0]),
        ]);
        $expected = $this->domainToArray($dictionaryEntry);

        $I->sendGET("/ltcs-home-visit-long-term-care-dictionary-entries/111111?providedIn={$providedIn}");

        $I->seeLogCount(0);
        $I->seeResponseCodeIs(HttpCode::OK);
        // 本当は下のコードでAssertできるはずだが、「Failed asserting that .」という不明なエラーメッセージでFailする
        // Api.phpでも別の方法でassertして回避してたのでここも同様に回避する.
        // $I->seeResponseContainsJson(['dictionaryEntry' => $expected]);
        $response = json_decode($I->grabResponse(), true);
        $I->assertEquals(['dictionaryEntry' => $expected], $response);
    }

    /**
     * ServiceCodeが存在しない場合に404が返るテスト.
     *
     * @param BillingTester $I
     */
    public function failWithNotFoundWhenServiceCodeIsInvalid(BillingTester $I)
    {
        $I->wantTo('fail with not found when service code is invalid');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $providedIn = '2021-11';

        $I->sendGET("/ltcs-home-visit-long-term-care-dictionary-entries/ZZAABB?providedIn={$providedIn}");

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, 'entry(serviceCode=ZZAABB) is not found');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }

    /**
     * 指定したサービス提供年月に対応する有効な辞書が存在しない場合に404が返るテスト.
     *
     * @param BillingTester $I
     */
    public function failWithNotFoundWhenProvidedInIsInvalid(BillingTester $I)
    {
        $I->wantTo('fail with not found when providedIn is invalid');

        $staff = $this->examples->staffs[0];
        $I->actingAs($staff);
        $providedIn = '2011-11';

        $I->sendGET("/ltcs-home-visit-long-term-care-dictionary-entries/118300?providedIn={$providedIn}");

        $I->seeLogCount(1);
        $I->seeLogMessage(0, LogLevel::WARNING, 'entry(serviceCode=118300) is not found');
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }
}
