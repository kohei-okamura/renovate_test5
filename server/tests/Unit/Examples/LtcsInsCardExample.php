<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Carbon;
use Domain\LtcsInsCard\LtcsCarePlanAuthorType;
use Domain\LtcsInsCard\LtcsInsCard;
use Domain\LtcsInsCard\LtcsInsCardMaxBenefitQuota;
use Domain\LtcsInsCard\LtcsInsCardServiceType;
use Domain\LtcsInsCard\LtcsInsCardStatus;
use Domain\LtcsInsCard\LtcsLevel;
use Faker\Generator;

/**
 * LtcsInsCard Example.
 *
 * @property-read \Domain\LtcsInsCard\LtcsInsCard[] $ltcsInsCards
 * @mixin \Tests\Unit\Examples\UserExample
 */
trait LtcsInsCardExample
{
    /**
     * Generate an example of LtcsInsCard.
     *
     * @param array $overwrites
     * @return \Domain\LtcsInsCard\LtcsInsCard
     */
    public function generateLtcsInsCard(array $overwrites): LtcsInsCard
    {
        /** @var \Faker\Generator $faker */
        $faker = app(Generator::class);
        $attrs = [
            'userId' => $this->users[3]->id,
            'status' => $faker->randomElement(LtcsInsCardStatus::all()),
            'insNumber' => '0123456789',
            'insurerNumber' => $faker->text(6),
            'insurerName' => $faker->text(100),
            // 事業対象者は支給限度基準額が今のところ算出できないので除外しておく
            // TODO: 事業対象者の支給限度基準額算出に対応したら `LtcsInsCardStatus::all()` にする
            'ltcsLevel' => $faker->randomElement([
                LtcsLevel::supportLevel1(),
                LtcsLevel::supportLevel2(),
                LtcsLevel::careLevel1(),
                LtcsLevel::careLevel2(),
                LtcsLevel::careLevel3(),
                LtcsLevel::careLevel4(),
                LtcsLevel::careLevel5(),
            ]),
            'maxBenefitQuotas' => [
                $this->maxBenefitQuota(),
                $this->maxBenefitQuota(),
            ],
            'copayRate' => $faker->numberBetween(0, 100),
            'effectivatedOn' => Carbon::instance($faker->dateTime('2021-01-25'))->startOfDay(),
            'issuedOn' => Carbon::instance($faker->dateTime('2021-01-25'))->startOfDay(),
            'certificatedOn' => Carbon::instance($faker->dateTime('2021-01-25'))->startOfDay(),
            'activatedOn' => Carbon::instance($faker->dateTime('2021-01-25'))->startOfDay(),
            'deactivatedOn' => Carbon::instance($faker->dateTime('2021-01-25'))->startOfDay(),
            'copayActivatedOn' => Carbon::instance($faker->dateTime('2021-01-25'))->startOfDay(),
            'copayDeactivatedOn' => Carbon::instance($faker->dateTime('2021-01-25'))->startOfDay(),
            'careManagerName' => $faker->text(100),
            'carePlanAuthorType' => LtcsCarePlanAuthorType::careManagerOffice(),
            'carePlanAuthorOfficeId' => $this->offices[0]->id,
            'communityGeneralSupportCenterId' => $this->offices[0]->id,
            'isEnabled' => $faker->boolean,
            'version' => 1,
            'updatedAt' => Carbon::instance($faker->dateTime('2021-01-25')),
            'createdAt' => Carbon::instance($faker->dateTime('2021-01-25')),
        ];
        return LtcsInsCard::create($overwrites + $attrs);
    }

    /**
     * 介護保険被保険者証の一覧を生成する.
     *
     * @return \Domain\LtcsInsCard\LtcsInsCard[]
     */
    protected function ltcsInsCards(): array
    {
        return [
            $this->generateLtcsInsCard(['id' => 1]),
            $this->generateLtcsInsCard(['id' => 2]),
            $this->generateLtcsInsCard(['id' => 3]),
            $this->generateLtcsInsCard(['id' => 4]),
            $this->generateLtcsInsCard(['id' => 5]),
            $this->generateLtcsInsCard(['id' => 6]),
            $this->generateLtcsInsCard(['id' => 7]),
            $this->generateLtcsInsCard(['id' => 8]),
            $this->generateLtcsInsCard(['id' => 9]),
            $this->generateLtcsInsCard(['id' => 10]),
            $this->generateLtcsInsCard([
                'id' => 11,
                'userId' => $this->users[10]->id,
                'effectivatedOn' => Carbon::parse('2020-10-10'),
            ]),
            $this->generateLtcsInsCard([
                'id' => 12,
                'userId' => $this->users[11]->id,
                'effectivatedOn' => Carbon::parse('2020-11-10'),
            ]),
            $this->generateLtcsInsCard([
                'id' => 13,
                'userId' => $this->users[14]->id,
                'effectivatedOn' => Carbon::parse('2021-10-10'),
            ]),
            $this->generateLtcsInsCard([
                'id' => 14,
                'userId' => $this->users[15]->id,
                'effectivatedOn' => Carbon::parse('2021-11-10'),
                'maxBenefitQuotas' => [
                    $this->maxBenefitQuota(),
                    $this->maxBenefitQuota(),
                    $this->maxBenefitQuota(),
                    $this->maxBenefitQuota(),
                ],
            ]),
            $this->generateLtcsInsCard(['id' => 15, 'userId' => $this->users[2]->id]),
            $this->generateLtcsInsCard([
                'id' => 16,
                'userId' => $this->users[0]->id,
                'status' => LtcsInsCardStatus::approved(),
                'effectivatedOn' => Carbon::parse('2020-01-01'),
                'ltcsLevel' => LtcsLevel::careLevel4(),
                'copayRate' => 30,
                'carePlanAuthorType' => LtcsCarePlanAuthorType::careManagerOffice(),
                'carePlanAuthorOfficeId' => $this->offices[20]->id,
                'communityGeneralSupportCenterId' => $this->offices[20]->id,
            ]),
            $this->generateLtcsInsCard([
                'id' => 17,
                'userId' => $this->users[4]->id,
                'status' => LtcsInsCardStatus::approved(),
                'effectivatedOn' => Carbon::parse('2020-01-01'),
            ]),
            $this->generateLtcsInsCard([
                'id' => 18,
                'userId' => $this->users[19]->id,
                'status' => LtcsInsCardStatus::approved(),
                'effectivatedOn' => Carbon::parse('2020-01-01'),
                'ltcsLevel' => LtcsLevel::careLevel4(),
                'copayRate' => 30,
                'carePlanAuthorType' => LtcsCarePlanAuthorType::careManagerOffice(),
                'carePlanAuthorOfficeId' => $this->offices[20]->id,
                'communityGeneralSupportCenterId' => $this->offices[20]->id,
            ]),
        ];
    }

    /**
     * maxBenefitQuota のフェイクデータを作成する.
     *
     * @return \Domain\LtcsInsCard\LtcsInsCardMaxBenefitQuota
     */
    private function maxBenefitQuota(): LtcsInsCardMaxBenefitQuota
    {
        $faker = app(Generator::class);
        return LtcsInsCardMaxBenefitQuota::create([
            'ltcsInsCardServiceType' => $faker->randomElement(LtcsInsCardServiceType::all()),
            'maxBenefitQuota' => $faker->randomNumber(),
        ]);
    }
}
