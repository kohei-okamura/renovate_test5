<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\LtcsInsCard;

use Domain\Common\Carbon;
use Domain\LtcsInsCard\LtcsCarePlanAuthorType;
use Domain\LtcsInsCard\LtcsInsCard;
use Domain\LtcsInsCard\LtcsLevel;
use Illuminate\Support\Arr;
use ScalikePHP\Option;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Examples\OrganizationExample;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * LtcsInsCard のテスト
 */
class LtcsInsCardTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use OrganizationExample;
    use UnitSupport;
    use MatchesSnapshots;

    protected LtcsInsCard $ltcsInsCard;

    protected array $values = [];

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (LtcsInsCardTest $self): void {
            $self->values = [
                'id' => 1,
                'userId' => $self->examples->users[0]->id,
                'effectivatedOn' => '2016-05-02 00:00:00',
                'status' => 2,
                'insNumber' => '0123456789',
                'issuedOn' => '2016-05-01 00:00:00',
                'insurerNumber' => '123456',
                'insurerName' => '佐藤太郎',
                'ltcsLevel' => 12,
                'certificatedOn' => '2016-05-01 00:00:00',
                'activatedOn' => '2016-05-01 00:00:00',
                'deactivatedOn' => '2016-05-01 00:00:00',
                'maxBenefitQuotas' => [1, 1],
                'copayRate' => '123',
                'copayActivatedOn' => '2016-05-01 00:00:00',
                'copayDeactivatedOn' => '2016-05-01 00:00:00',
                'careManagerName' => '鈴木太郎',
                'carePlanAuthorType' => LtcsCarePlanAuthorType::careManagerOffice(),
                'communityGeneralSupportCenterId' => $self->examples->offices[0]->id,
                'carePlanAuthorOfficeId' => $self->examples->offices[0]->id,
                'isEnabled' => 1,
                'version' => 1,
                'createdAt' => Carbon::now(),
                'updatedAt' => Carbon::now(),
            ];
            $self->ltcsInsCard = LtcsInsCard::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_levelHasBeenChanged(): void
    {
        $this->should('return true when id and level is different', function (): void {
            $this->assertTrue(LtcsInsCard::levelHasBeenChanged(
                Option::from(LtcsInsCard::create(['id' => 1, 'ltcsLevel' => LtcsLevel::careLevel4()])),
                LtcsInsCard::create(['id' => 2, 'ltcsLevel' => LtcsLevel::careLevel5()])
            ));
        });
        $this->should('return false when level is not changed', function (): void {
            $this->assertFalse(LtcsInsCard::levelHasBeenChanged(
                Option::from(LtcsInsCard::create(['id' => 1, 'ltcsLevel' => LtcsLevel::careLevel4()])),
                LtcsInsCard::create(['id' => 2, 'ltcsLevel' => LtcsLevel::careLevel4()])
            ));
        });
        $this->should('return false when insCardAtFirstOfMonth is none', function (): void {
            $this->assertFalse(LtcsInsCard::levelHasBeenChanged(
                Option::none(),
                LtcsInsCard::create(['id' => 2, 'ltcsLevel' => LtcsLevel::careLevel4()])
            ));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_greaterThanForLevel(): void
    {
        $this->should('return true when this level is greater than that level', function (): void {
            $insCard1 = LtcsInsCard::create(['ltcsLevel' => LtcsLevel::careLevel3()]);
            $insCard2 = LtcsInsCard::create(['ltcsLevel' => LtcsLevel::careLevel2()]);
            $this->assertTrue($insCard1->greaterThanForLevel($insCard2));
        });
        $this->should('return false when this level is not greater than that level', function (): void {
            $insCard1 = LtcsInsCard::create(['ltcsLevel' => LtcsLevel::careLevel2()]);
            $insCard2 = LtcsInsCard::create(['ltcsLevel' => LtcsLevel::careLevel2()]);
            $this->assertFalse($insCard1->greaterThanForLevel($insCard2));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should('have id attribute', function (): void {
            $this->assertSame($this->ltcsInsCard->id, Arr::get($this->values, 'id'));
        });
        $this->should('have userId attribute', function (): void {
            $this->assertSame($this->ltcsInsCard->userId, Arr::get($this->values, 'userId'));
        });
        $this->should('have effectivatedOn attribute', function (): void {
            $this->assertSame($this->ltcsInsCard->effectivatedOn, Arr::get($this->values, 'effectivatedOn'));
        });
        $this->should('have status attribute', function (): void {
            $this->assertSame($this->ltcsInsCard->status, Arr::get($this->values, 'status'));
        });
        $this->should('have insNumber attribute', function (): void {
            $this->assertSame($this->ltcsInsCard->insNumber, Arr::get($this->values, 'insNumber'));
        });
        $this->should('have issuedOn attribute', function (): void {
            $this->assertSame($this->ltcsInsCard->issuedOn, Arr::get($this->values, 'issuedOn'));
        });
        $this->should('have insurerNumber attribute', function (): void {
            $this->assertSame($this->ltcsInsCard->insurerNumber, Arr::get($this->values, 'insurerNumber'));
        });
        $this->should('have insurerName attribute', function (): void {
            $this->assertSame($this->ltcsInsCard->insurerName, Arr::get($this->values, 'insurerName'));
        });
        $this->should('have ltcsLevel attribute', function (): void {
            $this->assertSame($this->ltcsInsCard->ltcsLevel, Arr::get($this->values, 'ltcsLevel'));
        });
        $this->should('have certificatedOn attribute', function (): void {
            $this->assertSame($this->ltcsInsCard->certificatedOn, Arr::get($this->values, 'certificatedOn'));
        });
        $this->should('have activatedOn attribute', function (): void {
            $this->assertSame($this->ltcsInsCard->activatedOn, Arr::get($this->values, 'activatedOn'));
        });
        $this->should('have deactivatedOn attribute', function (): void {
            $this->assertSame($this->ltcsInsCard->deactivatedOn, Arr::get($this->values, 'deactivatedOn'));
        });
        $this->should('have maxBenefitQuotas attribute', function (): void {
            $this->assertSame($this->ltcsInsCard->maxBenefitQuotas, Arr::get($this->values, 'maxBenefitQuotas'));
        });
        $this->should('have copayRate attribute', function (): void {
            $this->assertSame($this->ltcsInsCard->copayRate, Arr::get($this->values, 'copayRate'));
        });
        $this->should('have copayActivatedOn attribute', function (): void {
            $this->assertSame($this->ltcsInsCard->copayActivatedOn, Arr::get($this->values, 'copayActivatedOn'));
        });
        $this->should('have copayDeactivatedOn attribute', function (): void {
            $this->assertSame(
                $this->ltcsInsCard->copayDeactivatedOn,
                Arr::get($this->values, 'copayDeactivatedOn')
            );
        });
        $this->should('have careManagerName attribute', function (): void {
            $this->assertSame($this->ltcsInsCard->careManagerName, Arr::get($this->values, 'careManagerName'));
        });
        $this->should('have carePlanAuthorType attribute', function (): void {
            $this->assertSame($this->ltcsInsCard->carePlanAuthorType, Arr::get($this->values, 'carePlanAuthorType'));
        });
        $this->should('have carePlanAuthorOfficeId attribute', function (): void {
            $this->assertSame(
                $this->ltcsInsCard->carePlanAuthorOfficeId,
                Arr::get($this->values, 'carePlanAuthorOfficeId')
            );
        });
        $this->should('have isEnabled attribute', function (): void {
            $this->assertSame($this->ltcsInsCard->isEnabled, Arr::get($this->values, 'isEnabled'));
        });
        $this->should('have version attribute', function (): void {
            $this->assertSame($this->ltcsInsCard->version, Arr::get($this->values, 'version'));
        });
        $this->should('have createdAt attribute', function (): void {
            $this->assertSame($this->ltcsInsCard->createdAt, Arr::get($this->values, 'createdAt'));
        });
        $this->should('have updatedAt attribute', function (): void {
            $this->assertSame($this->ltcsInsCard->updatedAt, Arr::get($this->values, 'updatedAt'));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->ltcsInsCard);
        });
    }
}
