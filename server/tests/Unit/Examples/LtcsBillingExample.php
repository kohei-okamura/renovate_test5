<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Billing\LtcsBilling;
use Domain\Billing\LtcsBillingFile;
use Domain\Billing\LtcsBillingOffice;
use Domain\Billing\LtcsBillingStatus;
use Domain\Common\Carbon;
use Domain\Common\MimeType;
use Faker\Generator as FakerGenerator;
use Tests\Unit\Faker\Faker;

/**
 * {@link \Domain\Billing\LtcsBilling} Examples.
 *
 * @mixin \Tests\Unit\Examples\OrganizationExample
 * @mixin \Tests\Unit\Examples\OfficeExample
 * @property-read \Domain\Billing\LtcsBilling[] $ltcsBillings
 */
trait LtcsBillingExample
{
    /**
     * 介護保険サービス：請求書を生成する.
     *
     * @param \Faker\Generator $faker
     * @param array $attrs
     * @return \Domain\Billing\LtcsBilling
     */
    public function generateLtcsBilling(FakerGenerator $faker, array $attrs): LtcsBilling
    {
        $values = [
            'organizationId' => 1,
            'office' => LtcsBillingOffice::from($this->offices[0]),
            'transactedIn' => Carbon::parse($faker->date()),
            'files' => [
                new LtcsBillingFile(
                    name: 'filename.pdf',
                    path: 'dummy/path',
                    token: $faker->text(60),
                    mimeType: $faker->randomElement(MimeType::all()),
                    createdAt: Carbon::instance($faker->dateTime),
                    downloadedAt: Carbon::instance($faker->dateTime),
                ),
            ],
            'status' => $faker->randomElement(LtcsBillingStatus::all()),
            'fixedAt' => Carbon::instance($faker->dateTime),
            'createdAt' => Carbon::instance($faker->dateTime),
            'updatedAt' => Carbon::instance($faker->dateTime),
        ];
        return LtcsBilling::create($attrs + $values);
    }

    /**
     * 介護保険サービス：請求書の一覧を生成する.
     *
     * @return array|\Domain\Billing\LtcsBilling[]
     */
    protected function ltcsBillings(): array
    {
        $faker = Faker::make(1283839334);
        return [
            $this->generateLtcsBilling($faker, [
                'id' => 1,
                'status' => LtcsBillingStatus::ready(),
                'transactedIn' => Carbon::create(1995, 11),
            ]),
            $this->generateLtcsBilling($faker, ['id' => 2]),
            $this->generateLtcsBilling($faker, ['id' => 3]),
            $this->generateLtcsBilling($faker, ['id' => 4]),
            $this->generateLtcsBilling($faker, [
                'id' => 5,
                'organizationId' => $this->organizations[1]->id,
            ]),
            $this->generateLtcsBilling($faker, [
                'id' => 6,
                'office' => LtcsBillingOffice::from($this->offices[2]),
            ]),
            $this->generateLtcsBilling($faker, [
                'id' => 7,
                'transactedIn' => Carbon::create(2021, 3, 1),
                'status' => LtcsBillingStatus::fixed(),
            ]),
            // 請求額 0 円で使っている
            $this->generateLtcsBilling($faker, ['id' => 8]),
            // /ltcs-billings/{id}/status のテスト (Cest) で使っている
            $this->generateLtcsBilling($faker, [
                'id' => 9,
                'transactedIn' => Carbon::create(2022, 2, 1),
                'files' => [],
                'status' => LtcsBillingStatus::ready(),
                'fixedAt' => null,
            ]),
        ];
    }
}
