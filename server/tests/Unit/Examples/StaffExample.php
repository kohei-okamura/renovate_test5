<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Carbon;
use Domain\Common\Location;
use Domain\Common\Password;
use Domain\Common\Sex;
use Domain\Common\StructuredName;
use Domain\Staff\Certification;
use Domain\Staff\Staff;
use Domain\Staff\StaffStatus;
use Faker\Generator;

/**
 * Staff Examples.
 *
 * @property-read Staff[] $staffs
 * @mixin \Tests\Unit\Examples\OrganizationExample
 * @mixin \Tests\Unit\Examples\RoleExample
 */
trait StaffExample
{
    /**
     * スタッフの一覧を生成する.
     *
     * @return \Domain\Staff\Staff[]
     */
    protected function staffs(): array
    {
        $password = Password::fromString('PassWoRD');
        return [
            $this->generateStaff([
                'id' => 1,
                'organizationId' => $this->organizations[0]->id,
                'bankAccountId' => $this->bankAccounts[0]->id,
                'email' => 'eustylelab@example.com',
                'isEnabled' => true,
                'isVerified' => true,
                'password' => $password,
                'status' => StaffStatus::active(),
            ]),
            $this->generateStaff([
                'id' => 2,
                'organizationId' => $this->organizations[1]->id,
                'bankAccountId' => $this->bankAccounts[1]->id,
                'email' => 'exampleuser1@example.com',
                'isEnabled' => true,
                'isVerified' => true,
                'password' => $password,
                'status' => StaffStatus::active(),
            ]),
            $this->generateStaff([
                'id' => 3,
                'organizationId' => $this->organizations[0]->id,
                'bankAccountId' => $this->bankAccounts[2]->id,
                'isEnabled' => true,
                'isVerified' => true,
                'password' => $password,
            ]),
            $this->generateStaff([
                'id' => 4,
                'organizationId' => $this->organizations[0]->id,
                'officeIds' => [],
                'bankAccountId' => $this->bankAccounts[3]->id,
                'name' => new StructuredName(
                    familyName: '内藤',
                    givenName: '勇介',
                    phoneticFamilyName: 'ナイトウ',
                    phoneticGivenName: 'ユウスケ',
                ),
            ]),
            $this->generateStaff([
                'id' => 5,
                'organizationId' => $this->organizations[0]->id,
                'bankAccountId' => $this->bankAccounts[4]->id,
                'name' => new StructuredName(
                    familyName: '内藤',
                    givenName: '太郎',
                    phoneticFamilyName: 'ナイトウ',
                    phoneticGivenName: 'タロウ',
                ),
                'officeIds' => [$this->offices[2]->id],
                'isEnabled' => true,
                'isVerified' => true,
                'roleIds' => [$this->roles[6]->id],
                'officeGroupIds' => [$this->officeGroups[0]->id],
                'password' => $password,
            ]),
            $this->generateStaff([
                'id' => 6,
                'organizationId' => $this->organizations[0]->id,
                'name' => new StructuredName(
                    familyName: '内藤',
                    givenName: '花子',
                    phoneticFamilyName: 'ナイトウ',
                    phoneticGivenName: 'ハナコ',
                ),
            ]),
            $this->generateStaff([
                'id' => 7,
                'organizationId' => $this->organizations[0]->id,
                'name' => new StructuredName(
                    familyName: '内藤',
                    givenName: '美智子',
                    phoneticFamilyName: 'ナイトウ',
                    phoneticGivenName: 'ミチコ',
                ),
            ]),
            $this->generateStaff([
                'id' => 8,
                'organizationId' => $this->organizations[0]->id,
                'name' => new StructuredName(
                    familyName: '山田',
                    givenName: '勇介',
                    phoneticFamilyName: 'ヤマダ',
                    phoneticGivenName: 'ユウスケ',
                ),
            ]),
            $this->generateStaff([
                'id' => 9,
                'organizationId' => $this->organizations[0]->id,
                'name' => new StructuredName(
                    familyName: '山田',
                    givenName: '太郎',
                    phoneticFamilyName: 'ヤマダ',
                    phoneticGivenName: 'タロウ',
                ),
            ]),
            $this->generateStaff([
                'id' => 10,
                'organizationId' => $this->organizations[0]->id,
                'name' => new StructuredName(
                    familyName: '山田',
                    givenName: '花子',
                    phoneticFamilyName: 'ヤマダ',
                    phoneticGivenName: 'ハナコ',
                ),
            ]),
            $this->generateStaff([
                'id' => 11,
                'organizationId' => $this->organizations[0]->id,
                'name' => new StructuredName(
                    familyName: '山田',
                    givenName: '美智子',
                    phoneticFamilyName: 'ヤマダ',
                    phoneticGivenName: 'ミチコ',
                ),
            ]),
            $this->generateStaff([
                'id' => 12,
                'organizationId' => $this->organizations[0]->id,
                'name' => new StructuredName(
                    familyName: '田中',
                    givenName: '勇介',
                    phoneticFamilyName: 'タナカ',
                    phoneticGivenName: 'ユウスケ',
                ),
            ]),
            $this->generateStaff([
                'id' => 13,
                'organizationId' => $this->organizations[0]->id,
                'name' => new StructuredName(
                    familyName: '田中',
                    givenName: '太郎',
                    phoneticFamilyName: 'タナカ',
                    phoneticGivenName: 'タロウ',
                ),
            ]),
            $this->generateStaff([
                'id' => 14,
                'organizationId' => $this->organizations[0]->id,
                'name' => new StructuredName(
                    familyName: '田中',
                    givenName: '花子',
                    phoneticFamilyName: 'タナカ',
                    phoneticGivenName: 'ハナコ',
                ),
            ]),
            $this->generateStaff([
                'id' => 15,
                'organizationId' => $this->organizations[1]->id,
                'name' => new StructuredName(
                    familyName: '内藤',
                    givenName: '勇介',
                    phoneticFamilyName: 'ナイトウ',
                    phoneticGivenName: 'ユウスケ',
                ),
                'officeGroupIds' => [$this->officeGroups[0]->id],
            ]),
            $this->generateStaff([
                'id' => 16,
                'organizationId' => $this->organizations[4]->id,
                'name' => new StructuredName(
                    familyName: 'ハーモニータワー',
                    givenName: 'スタッフ緯度経度検索テスト',
                    phoneticFamilyName: 'ハーモニータワー',
                    phoneticGivenName: 'スタッフイドケイドケンサクテスト',
                ),
                'sex' => Sex::male(),
                'location' => Location::create([
                    'lat' => 35.696208,
                    'lng' => 139.683435,
                ]),
            ]),
            $this->generateStaff([
                'id' => 17,
                'organizationId' => $this->organizations[4]->id,
                'name' => new StructuredName(
                    familyName: '成願寺',
                    givenName: 'スタッフ緯度経度検索テスト',
                    phoneticFamilyName: 'セイガンジ',
                    phoneticGivenName: 'スタッフイドケイドケンサクテスト',
                ),
                'sex' => Sex::female(),
                'location' => Location::create([
                    'lat' => 35.693850,
                    'lng' => 139.681510,
                ]),
                'isEnabled' => false,
            ]),
            $this->generateStaff([
                'id' => 18,
                'organizationId' => $this->organizations[0]->id,
                'name' => new StructuredName(
                    familyName: 'ヒルトン東京',
                    givenName: 'スタッフ緯度経度検索テスト',
                    phoneticFamilyName: 'ヒルトントウキョウ',
                    phoneticGivenName: 'スタッフイドケイドケンサクテスト',
                ),
                'sex' => Sex::male(),
                'location' => Location::create([
                    'lat' => 35.692656,
                    'lng' => 139.691192,
                ]),
            ]),
            $this->generateStaff([
                'id' => 19,
                'organizationId' => $this->organizations[1]->id,
                'name' => new StructuredName(
                    familyName: '東京都庁',
                    givenName: 'スタッフ緯度経度検索テスト',
                    phoneticFamilyName: 'トウキョウトチョウ',
                    phoneticGivenName: 'スタッフイドケイドケンサクテスト',
                ),
                'sex' => Sex::female(),
                'location' => Location::create([
                    'lat' => 35.689680,
                    'lng' => 139.692095,
                ]),
            ]),
            $this->generateStaff([
                'id' => 20,
                'organizationId' => $this->organizations[4]->id,
                'name' => new StructuredName(
                    familyName: '伊勢丹新宿店',
                    givenName: 'スタッフ緯度経度検索テスト',
                    phoneticFamilyName: 'イセタンシンジュクテン',
                    phoneticGivenName: 'スタッフイドケイドケンサクテスト',
                ),
                'sex' => Sex::male(),
                'location' => Location::create([
                    'lat' => 35.692056,
                    'lng' => 139.704503,
                ]),
            ]),
            $this->generateStaff([
                'id' => 21,
                'organizationId' => $this->organizations[0]->id,
                'name' => new StructuredName(
                    familyName: '新宿御苑',
                    givenName: 'スタッフ緯度経度検索テスト',
                    phoneticFamilyName: 'シンジュクギョエン',
                    phoneticGivenName: 'スタッフイドケイドケンサクテスト',
                ),
                'sex' => Sex::male(),
                'location' => Location::create([
                    'lat' => 35.685197,
                    'lng' => 139.710006,
                ]),
            ]),
            $this->generateStaff([
                'id' => 22,
                'organizationId' => $this->organizations[4]->id,
                'name' => new StructuredName(
                    familyName: '渋谷スクランブル交差点',
                    givenName: 'スタッフ緯度経度検索テスト',
                    phoneticFamilyName: 'シブヤスクランブルコウサテン',
                    phoneticGivenName: 'スタッフイドケイドケンサクテスト',
                ),
                'sex' => Sex::male(),
                'location' => Location::create([
                    'lat' => 35.659517,
                    'lng' => 139.700571,
                ]),
            ]),
            $this->generateStaff([
                'id' => 23,
                'organizationId' => $this->organizations[4]->id,
                'name' => new StructuredName(
                    familyName: '六本木ヒルズ',
                    givenName: 'スタッフ緯度経度検索テスト',
                    phoneticFamilyName: 'ロッポンギヒルズ',
                    phoneticGivenName: 'スタッフイドケイドケンサクテスト',
                ),
                'sex' => Sex::male(),
                'location' => Location::create([
                    'lat' => 35.660355,
                    'lng' => 139.730063,
                ]),
            ]),
            $this->generateStaff([
                'id' => 24,
                'organizationId' => $this->organizations[4]->id,
                'name' => new StructuredName(
                    familyName: 'グランドプリンスホテル新高輪',
                    givenName: 'スタッフ緯度経度検索テスト',
                    phoneticFamilyName: 'グランドプリンスホテルシンタカナワ',
                    phoneticGivenName: 'スタッフイドケイドケンサクテスト',
                ),
                'sex' => Sex::male(),
                'location' => Location::create([
                    'lat' => 35.630193,
                    'lng' => 139.733535,
                ]),
            ]),
            $this->generateStaff([
                'id' => 25,
                'organizationId' => $this->organizations[4]->id,
                'name' => new StructuredName(
                    familyName: '皇居',
                    givenName: 'スタッフ緯度経度検索テスト',
                    phoneticFamilyName: 'コウキョ',
                    phoneticGivenName: 'スタッフイドケイドケンサクテスト',
                ),
                'sex' => Sex::male(),
                'location' => Location::create([
                    'lat' => 35.685297,
                    'lng' => 139.752769,
                ]),
                'officeIds' => [$this->offices[1]->id],
            ]),
            $this->generateStaff([
                'id' => 26,
                'organizationId' => $this->organizations[4]->id,
                'name' => new StructuredName(
                    familyName: 'アクアシティお台場',
                    givenName: 'スタッフ緯度経度検索テスト',
                    phoneticFamilyName: 'アクアシティオダイバ',
                    phoneticGivenName: 'スタッフイドケイドケンサクテスト',
                ),
                'sex' => Sex::male(),
                'location' => Location::create([
                    'lat' => 35.627912,
                    'lng' => 139.773514,
                ]),
                'officeIds' => [$this->offices[1]->id],
            ]),
            $this->generateStaff([
                'id' => 27,
                'organizationId' => $this->organizations[4]->id,
                'name' => new StructuredName(
                    familyName: '東京ディズニーランド',
                    givenName: 'スタッフ緯度経度検索テスト',
                    phoneticFamilyName: 'トウキョウディズニーランド',
                    phoneticGivenName: 'スタッフイドケイドケンサクテスト',
                ),
                'sex' => Sex::female(),
                'location' => Location::create([
                    'lat' => 35.632931,
                    'lng' => 139.880405,
                ]),
                'officeIds' => [$this->offices[1]->id],
            ]),
            $this->generateStaff([
                'id' => 28,
                'organizationId' => $this->organizations[0]->id,
                'name' => new StructuredName(
                    familyName: '竹山',
                    givenName: '崇志',
                    phoneticFamilyName: 'タケヤマ',
                    phoneticGivenName: 'タカシ',
                ),
                'sex' => Sex::male(),
                'location' => Location::create([
                    'lat' => 0.0,
                    'lng' => 0.0,
                ]),
                'officeIds' => [$this->offices[1]->id],
                'bankAccountId' => $this->bankAccounts[0]->id,
                'email' => 'eustylelab1@example.com',
                'isEnabled' => true,
                'isVerified' => true,
                'roleIds' => [$this->roles[1]->id], // viewしかできない役割
                'password' => $password,
            ]),
            $this->generateStaff([
                'id' => 29,
                'organizationId' => $this->organizations[0]->id,
                'bankAccountId' => $this->bankAccounts[0]->id,
                'email' => 'eustylelab2@example.com',
                'isEnabled' => true,
                'isVerified' => true,
                'roleIds' => [$this->roles[8]->id], // 事業所管理者
                'password' => $password,
                'officeIds' => [$this->offices[0]->id],
            ]),
            $this->generateStaff([
                'id' => 30,
                'organizationId' => $this->organizations[0]->id,
                'bankAccountId' => $this->bankAccounts[0]->id,
                'email' => 'eustylelab3@example.com',
                'isEnabled' => true,
                'isVerified' => true,
                'roleIds' => [$this->roles[11]->id], // ヘルパー
                'password' => $password,
            ]),
            $this->generateStaff([
                'id' => 31,
                'organizationId' => $this->organizations[0]->id,
                'bankAccountId' => $this->bankAccounts[0]->id,
                'email' => 'eustylelab4@example.com',
                'isEnabled' => true,
                'isVerified' => true,
                'roleIds' => [$this->roles[12]->id], // 管理者
                'password' => $password,
            ]),
            $this->generateStaff([
                'id' => 32,
                'organizationId' => $this->organizations[0]->id,
                'bankAccountId' => $this->bankAccounts[0]->id,
                'isEnabled' => true,
                'isVerified' => true,
                'roleIds' => [$this->roles[13]->id], // 権限無し
                'password' => $password,
            ]),
            $this->generateStaff([
                'id' => 33,
                'organizationId' => $this->organizations[0]->id,
                'bankAccountId' => $this->bankAccounts[0]->id,
                'isEnabled' => true,
                'isVerified' => true,
                'roleIds' => [
                    $this->roles[8]->id, // 事業所管理者
                    $this->roles[14]->id, // 契約担当者
                ],
                'password' => $password,
            ]),
            $this->generateStaff([
                'id' => 34,
                'organizationId' => $this->organizations[0]->id,
                'bankAccountId' => $this->bankAccounts[0]->id,
                'isEnabled' => true,
                'isVerified' => true,
                'roleIds' => [
                    $this->roles[15]->id, // 事業所グループ一覧権限を持っているが office の権限
                ],
                'password' => $password,
            ]),
            // 無効なスタッフ
            $this->generateStaff([
                'id' => 35,
                'organizationId' => $this->organizations[0]->id,
                'email' => 'id_35_staff@example.com',
                'isEnabled' => false,
                'status' => StaffStatus::retired(),
            ]),
        ];
    }

    /**
     * Generate an example of Staff.
     *
     * @param array $overwrites
     * @return \Domain\Staff\Staff
     */
    private function generateStaff(array $overwrites)
    {
        /** @var Generator $faker */
        $faker = app(Generator::class);
        $sex = $faker->randomElement([Sex::male(), Sex::female()]);
        $values = [
            'employeeNumber' => $faker->numerify(str_repeat('#', 4)),
            'name' => $faker->name($sex),
            'sex' => $faker->randomElement(Sex::all()),
            'birthday' => Carbon::instance($faker->dateTime)->startOfDay(),
            'addr' => $faker->addr,
            'location' => Location::create([
                'lat' => $faker->randomFloat(6, -90, 90),
                'lng' => $faker->randomFloat(6, -180, 180),
            ]),
            'tel' => '01-2345-6789',
            'fax' => '01-2000-6789',
            'email' => $faker->emailAddress,
            'password' => Password::fromString($faker->password),
            'certifications' => $faker->randomElements(Certification::all()),
            'bankAccountId' => $this->bankAccounts[0]->id,
            'roleIds' => [$this->roles[0]->id],
            'officeIds' => [$this->offices[0]->id],
            'officeGroupIds' => [],
            'isVerified' => $faker->boolean,
            'status' => StaffStatus::active(),
            'isEnabled' => $faker->boolean,
            'version' => 1,
            'createdAt' => Carbon::instance($faker->dateTime),
            'updatedAt' => Carbon::instance($faker->dateTime),
        ];
        return Staff::create($overwrites + $values);
    }
}
