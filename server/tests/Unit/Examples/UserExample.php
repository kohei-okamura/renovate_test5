<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Addr;
use Domain\Common\Carbon;
use Domain\Common\Contact;
use Domain\Common\ContactRelationship;
use Domain\Common\Location;
use Domain\Common\Prefecture;
use Domain\Common\Sex;
use Domain\Common\StructuredName;
use Domain\User\BillingDestination;
use Domain\User\PaymentMethod;
use Domain\User\User;
use Domain\User\UserBillingDestination;
use Faker\Generator;

/**
 * User Example.
 *
 * @property-read \Domain\User\User[] $users
 * @mixin \Tests\Unit\Examples\OrganizationExample
 */
trait UserExample
{
    /**
     * Generate an example of User.
     *
     * @param array $overwrites
     * @return \Domain\User\User
     */
    public function generateUser(array $overwrites): User
    {
        /** @var Generator $faker */
        $faker = app(Generator::class);
        $sex = $faker->randomElement([Sex::male(), Sex::female()]);
        $values = [
            'bankAccountId' => $this->bankAccounts[0]->id,
            'name' => $faker->name($sex),
            'sex' => $sex,
            'birthday' => Carbon::instance($faker->dateTime)->startOfDay(),
            'addr' => $faker->addr,
            'location' => Location::create([
                'lat' => $faker->randomFloat(6, -90, 90),
                'lng' => $faker->randomFloat(6, -180, 180),
            ]),
            'contacts' => [
                Contact::create([
                    'tel' => '01-2345-6789',
                    'relationship' => ContactRelationship::family(),
                    'name' => '田中花子',
                ]),
            ],
            'billingDestination' => UserBillingDestination::create([
                'destination' => BillingDestination::agent(),
                'paymentMethod' => PaymentMethod::withdrawal(),
                'contractNumber' => '0123456789',
                'corporationName' => 'ユースタイルラボラトリー株式会社',
                'agentName' => '山田太郎',
                'addr' => new Addr(
                    postcode: '164-0011',
                    prefecture: Prefecture::tokyo(),
                    city: '中野区',
                    street: '中央1-35-6',
                    apartment: 'レッチフィールド中野坂上ビル6F',
                ),
                'tel' => '03-1234-5678',
            ]),
            'isVerified' => $faker->boolean,
            'isEnabled' => $faker->boolean,
            'version' => 1,
            'createdAt' => Carbon::instance($faker->dateTime),
            'updatedAt' => Carbon::instance($faker->dateTime),
        ];
        return User::create($overwrites + $values);
    }

    /**
     * 利用者の一覧を生成する.
     *
     * @return \Domain\User\User[]
     *
     * NOTE: [12]と[13]は、UserRepositoryのremove系のテストで使用するのでリレーションしない
     */
    protected function users(): array
    {
        return [
            $this->generateUser([
                'id' => 1,
                'organizationId' => $this->organizations[0]->id,
                'isEnabled' => true,
                'isVerified' => true,
            ]),
            $this->generateUser([
                'id' => 2,
                'organizationId' => $this->organizations[0]->id,
                'bankAccountId' => $this->bankAccounts[1]->id,
                'isEnabled' => true,
                'isVerified' => true,
            ]),
            $this->generateUser([
                'id' => 3,
                'organizationId' => $this->organizations[0]->id,
                'isEnabled' => true,
                'isVerified' => true,
            ]),
            $this->generateUser([
                'id' => 4,
                'organizationId' => $this->organizations[0]->id,
                'name' => new StructuredName(
                    familyName: '内藤',
                    givenName: '勇介',
                    phoneticFamilyName: 'ナイトウ',
                    phoneticGivenName: 'ユウスケ',
                ),
            ]),
            $this->generateUser([
                'id' => 5,
                'organizationId' => $this->organizations[0]->id,
                'name' => new StructuredName(
                    familyName: '内藤',
                    givenName: '太郎',
                    phoneticFamilyName: 'ナイトウ',
                    phoneticGivenName: 'タロウ',
                ),
            ]),
            $this->generateUser([
                'id' => 6,
                'organizationId' => $this->organizations[0]->id,
                'name' => new StructuredName(
                    familyName: '内藤',
                    givenName: '花子',
                    phoneticFamilyName: 'ナイトウ',
                    phoneticGivenName: 'ハナコ',
                ),
            ]),
            $this->generateUser([
                'id' => 7,
                'organizationId' => $this->organizations[0]->id,
                'name' => new StructuredName(
                    familyName: '内藤',
                    givenName: '美智子',
                    phoneticFamilyName: 'ナイトウ',
                    phoneticGivenName: 'ミチコ',
                ),
            ]),
            $this->generateUser([
                'id' => 8,
                'organizationId' => $this->organizations[0]->id,
                'name' => new StructuredName(
                    familyName: '山田',
                    givenName: '勇介',
                    phoneticFamilyName: 'ヤマダ',
                    phoneticGivenName: 'ユウスケ',
                ),
            ]),
            $this->generateUser([
                'id' => 9,
                'organizationId' => $this->organizations[0]->id,
                'name' => new StructuredName(
                    familyName: '山田',
                    givenName: '太郎',
                    phoneticFamilyName: 'ヤマダ',
                    phoneticGivenName: 'タロウ',
                ),
            ]),
            $this->generateUser([
                'id' => 10,
                'organizationId' => $this->organizations[0]->id,
                'name' => new StructuredName(
                    familyName: '山田',
                    givenName: '花子',
                    phoneticFamilyName: 'ヤマダ',
                    phoneticGivenName: 'ハナコ',
                ),
            ]),
            $this->generateUser([
                'id' => 11,
                'organizationId' => $this->organizations[0]->id,
                'name' => new StructuredName(
                    familyName: '山田',
                    givenName: '美智子',
                    phoneticFamilyName: 'ヤマダ',
                    phoneticGivenName: 'ミチコ',
                ),
            ]),
            $this->generateUser([
                'id' => 12,
                'organizationId' => $this->organizations[0]->id,
                'name' => new StructuredName(
                    familyName: '田中',
                    givenName: '勇介',
                    phoneticFamilyName: 'タナカ',
                    phoneticGivenName: 'ユウスケ',
                ),
            ]),
            $this->generateUser([
                'id' => 13,
                'organizationId' => $this->organizations[0]->id,
                'name' => new StructuredName(
                    familyName: '田中',
                    givenName: '太郎',
                    phoneticFamilyName: 'タナカ',
                    phoneticGivenName: 'タロウ',
                ),
            ]),
            $this->generateUser([
                'id' => 14,
                'organizationId' => $this->organizations[0]->id,
                'name' => new StructuredName(
                    familyName: '田中',
                    givenName: '花子',
                    phoneticFamilyName: 'タナカ',
                    phoneticGivenName: 'ハナコ',
                ),
            ]),
            $this->generateUser([
                'id' => 15,
                'organizationId' => $this->organizations[1]->id,
                'name' => new StructuredName(
                    familyName: '内藤',
                    givenName: '勇介',
                    phoneticFamilyName: 'ナイトウ',
                    phoneticGivenName: 'ユウスケ',
                ),
            ]),
            $this->generateUser([
                'id' => 16,
                'organizationId' => $this->organizations[4]->id,
                'name' => new StructuredName(
                    familyName: 'ユースタイル',
                    givenName: 'ラボラトリー',
                    phoneticFamilyName: 'ユースタイル',
                    phoneticGivenName: 'ラボラトリー',
                ),
                'sex' => Sex::male(),
                'location' => Location::create([
                    'lat' => 35.696929,
                    'lng' => 139.684812,
                ]),
            ]),
            $this->generateUser([
                'id' => 17,
                'organizationId' => $this->organizations[4]->id,
                'bankAccountId' => $this->bankAccounts[20]->id,
                'name' => new StructuredName(
                    familyName: '固定',
                    givenName: '値用',
                    phoneticFamilyName: 'こてい',
                    phoneticGivenName: 'ちよう',
                ),
                'sex' => Sex::male(),
                'location' => Location::create([
                    'lat' => 35.696929,
                    'lng' => 139.684812,
                ]),
                'addr' => new Addr(
                    postcode: '1640000',
                    prefecture: Prefecture::tokyo(),
                    city: '中野区',
                    street: 'ここどこ町',
                    apartment: '架空建物',
                ),
                'birthday' => Carbon::parse('2000-01-01'),
            ]),
            // 利用者負担上限額管理結果票の id: 8 と一緒に使っている
            // DwsBillingBundleId と CopayCoordination->user->userId で取得したデータの head を使うので、同条件のデータ重複がないようにしたい
            $this->generateUser([
                'id' => 18,
                'organizationId' => $this->organizations[1]->id,
            ]),
            // 利用者負担上限額管理結果票の id: 9 と一緒に使っている
            // DwsBillingBundleId と CopayCoordination->user->userId で取得したデータの head を使うので、同条件のデータ重複がないようにしたい
            $this->generateUser([
                'id' => 19,
                'organizationId' => $this->organizations[0]->id,
            ]),
            // 地域加算用
            $this->generateUser([
                'id' => 20,
                'organizationId' => $this->organizations[0]->id,
                'isEnabled' => true,
                'isVerified' => true,
            ]),
        ];
    }
}
