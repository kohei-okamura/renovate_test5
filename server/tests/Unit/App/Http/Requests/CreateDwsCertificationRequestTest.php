<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\CreateDwsCertificationRequest;
use App\Http\Requests\OrganizationRequest;
use App\Http\Requests\StaffRequest;
use Closure;
use Domain\Common\Carbon;
use Domain\Common\StructuredName;
use Domain\DwsCertification\Child;
use Domain\DwsCertification\CopayCoordination;
use Domain\DwsCertification\CopayCoordinationType;
use Domain\DwsCertification\DwsCertification;
use Domain\DwsCertification\DwsCertificationAgreement;
use Domain\DwsCertification\DwsCertificationAgreementType;
use Domain\DwsCertification\DwsCertificationGrant;
use Domain\DwsCertification\DwsCertificationServiceType;
use Domain\DwsCertification\DwsCertificationStatus;
use Domain\DwsCertification\DwsLevel;
use Domain\DwsCertification\DwsType;
use Illuminate\Support\Arr;
use Lib\Json;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\GetOfficeListUseCaseMixin;
use Tests\Unit\Mixins\LookupOfficeUseCaseMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationRepositoryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Http\Requests\CreateDwsCertificationRequest} のテスト.
 */
final class CreateDwsCertificationRequestTest extends Test
{
    use CarbonMixin;
    use ConfigMixin;
    use ExamplesConsumer;
    use GetOfficeListUseCaseMixin;
    use LookupOfficeUseCaseMixin;
    use MockeryMixin;
    use OrganizationRepositoryMixin;
    use UnitSupport;

    private CreateDwsCertificationRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachSpec(function (self $self): void {
            $self->getOfficeListUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();
            $self->lookupOfficeUseCase
                ->allows('handle')
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();

            $self->request = new CreateDwsCertificationRequest();
            OrganizationRequest::prepareOrganizationRequest(
                $self->request,
                $self->examples->organizations[0]
            );
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
        $this->specify(
            '全ての値が入力されている場合に DwsCertification を返す',
            function (): void {
                $input = self::input();
                $this->request->initialize(
                    server: ['CONTENT_TYPE' => 'application/json'],
                    content: Json::encode($input)
                );
                $expected = DwsCertification::create([
                    'effectivatedOn' => Carbon::create(2022, 4, 1),
                    'status' => DwsCertificationStatus::approved(),
                    'dwsNumber' => '0001287325',
                    'dwsTypes' => [
                        DwsType::physical(),
                        DwsType::intractableDiseases(),
                    ],
                    'issuedOn' => Carbon::create(2022, 4, 1),
                    'cityName' => '中野区',
                    'cityCode' => '131148',
                    'dwsLevel' => DwsLevel::level6(),
                    'isSubjectOfComprehensiveSupport' => true,
                    'activatedOn' => Carbon::create(2021, 4, 1),
                    'deactivatedOn' => Carbon::create(2024, 3, 31),
                    'grants' => [
                        DwsCertificationGrant::create([
                            'dwsCertificationServiceType' => DwsCertificationServiceType::physicalCare(),
                            'grantedAmount' => '999時間/月',
                            'activatedOn' => Carbon::create(2022, 1, 1),
                            'deactivatedOn' => Carbon::create(2022, 12, 31),
                        ]),
                    ],
                    'child' => Child::create([
                        'name' => new StructuredName(
                            familyName: '内藤',
                            givenName: '勇介',
                            phoneticFamilyName: 'ナイトウ',
                            phoneticGivenName: 'ユウスケ',
                        ),
                        'birthday' => Carbon::create(1985, 2, 24),
                    ]),
                    'copayRate' => 10,
                    'copayLimit' => 37200,
                    'copayActivatedOn' => Carbon::create(2022, 4, 1),
                    'copayDeactivatedOn' => Carbon::create(2023, 3, 31),
                    'copayCoordination' => CopayCoordination::create([
                        'copayCoordinationType' => CopayCoordinationType::internal(),
                        'officeId' => 13,
                    ]),
                    'agreements' => [
                        DwsCertificationAgreement::create([
                            'indexNumber' => '1',
                            'officeId' => 13,
                            'dwsCertificationAgreementType' => DwsCertificationAgreementType::physicalCare(),
                            'paymentAmount' => 6000, // 100時間（6,000分）
                            'agreedOn' => Carbon::create(2021, 9, 1),
                            'expiredOn' => Carbon::create(2023, 1, 31),
                        ]),
                    ],
                    'isEnabled' => true,
                    'version' => 1,
                    'createdAt' => Carbon::now(),
                    'updatedAt' => Carbon::now(),
                ]);

                $this->assertModelStrictEquals($expected, actual: $this->request->payload());
            }
        );
        $this->specify(
            '省略可能な値が未入力の場合でも DwsCertification を返す',
            function (Closure $f): void {
                $input = $f();
                $this->request->initialize(
                    server: ['CONTENT_TYPE' => 'application/json'],
                    content: Json::encode($input)
                );
                $expected = DwsCertification::create([
                    'effectivatedOn' => Carbon::create(2022, 4, 1),
                    'status' => DwsCertificationStatus::approved(),
                    'dwsNumber' => '0001287325',
                    'dwsTypes' => [
                        DwsType::physical(),
                        DwsType::intractableDiseases(),
                    ],
                    'issuedOn' => Carbon::create(2022, 4, 1),
                    'cityName' => '中野区',
                    'cityCode' => '131148',
                    'dwsLevel' => DwsLevel::level6(),
                    'isSubjectOfComprehensiveSupport' => true,
                    'activatedOn' => Carbon::create(2021, 4, 1),
                    'deactivatedOn' => Carbon::create(2024, 3, 31),
                    'grants' => [
                        DwsCertificationGrant::create([
                            'dwsCertificationServiceType' => DwsCertificationServiceType::physicalCare(),
                            'grantedAmount' => '999時間/月',
                            'activatedOn' => Carbon::create(2022, 1, 1),
                            'deactivatedOn' => Carbon::create(2022, 12, 31),
                        ]),
                    ],
                    'child' => Child::create([
                        'name' => new StructuredName(
                            familyName: '',
                            givenName: '',
                            phoneticFamilyName: '',
                            phoneticGivenName: '',
                        ),
                        'birthday' => null,
                    ]),
                    'copayRate' => 10,
                    'copayLimit' => 37200,
                    'copayActivatedOn' => Carbon::create(2022, 4, 1),
                    'copayDeactivatedOn' => Carbon::create(2023, 3, 31),
                    'copayCoordination' => CopayCoordination::create([
                        'copayCoordinationType' => CopayCoordinationType::none(),
                        'officeId' => null,
                    ]),
                    'agreements' => [
                        DwsCertificationAgreement::create([
                            'indexNumber' => '1',
                            'officeId' => 13,
                            'dwsCertificationAgreementType' => DwsCertificationAgreementType::physicalCare(),
                            'paymentAmount' => 6000, // 100時間（6,000分）
                            'agreedOn' => Carbon::create(2021, 9, 1),
                            'expiredOn' => null,
                        ]),
                    ],
                    'isEnabled' => true,
                ]);

                $this->assertModelStrictEquals($expected, actual: $this->request->payload());
            },
            [
                'examples' => [
                    '省略可能な値が空文字で入力された場合' => [
                        function (): array {
                            $values = $this->input();
                            Arr::set($values, 'child.name.familyName', '');
                            Arr::set($values, 'child.name.givenName', '');
                            Arr::set($values, 'child.name.phoneticFamilyName', '');
                            Arr::set($values, 'child.name.phoneticGivenName', '');
                            Arr::set($values, 'child.birthday', '');
                            Arr::set(
                                $values,
                                'copayCoordination.copayCoordinationType',
                                CopayCoordinationType::none()->value()
                            );
                            Arr::set($values, 'copayCoordination.officeId', '');
                            Arr::set($values, 'agreements.0.expiredOn', '');
                            return $values;
                        },
                    ],
                    '省略可能な値が null で入力された場合' => [
                        function (): array {
                            $values = $this->input();
                            Arr::set($values, 'child.name.familyName', null);
                            Arr::set($values, 'child.name.givenName', null);
                            Arr::set($values, 'child.name.phoneticFamilyName', null);
                            Arr::set($values, 'child.name.phoneticGivenName', null);
                            Arr::set($values, 'child.birthday', null);
                            Arr::set(
                                $values,
                                'copayCoordination.copayCoordinationType',
                                CopayCoordinationType::none()->value()
                            );
                            Arr::set($values, 'copayCoordination.officeId', null);
                            Arr::set($values, 'agreements.0.expiredOn', null);
                            return $values;
                        },
                    ],
                    '省略可能な値が入力されなかった場合' => [
                        function (): array {
                            $values = self::input([
                                'copayCoordination' => [
                                    'copayCoordinationType' => CopayCoordinationType::none()->value(),
                                    'officeId' => null,
                                ],
                            ]);
                            Arr::forget($values, 'child');
                            Arr::forget($values, 'copayCoordination.officeId');
                            Arr::forget($values, 'agreements.0.expiredOn');
                            return $values;
                        },
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
        $this->specify(
            'バリデーションを通過する',
            function (array $values): void {
                $input = self::input($values);
                $validator = $this->request->createValidatorInstance($input);

                $actual = $validator->passes();

                $this->assertSame([], $validator->errors()->toArray());
                $this->assertTrue($actual);
            },
            ['examples' => $this->passedValidationExamples()]
        );
        $this->specify(
            'バリデーションを通過しない',
            function (array $invalidValues, array $validValues, array $expectedErrors, Closure $f = null): void {
                $input = self::input();
                foreach ($invalidValues as $key => $value) {
                    Arr::set($input, $key, $value);
                }
                if ($f !== null) {
                    $f();
                }
                $validator = $this->request->createValidatorInstance($input);

                $actual = $validator->fails();

                $this->assertSame($expectedErrors, $validator->errors()->toArray());
                $this->assertTrue($actual);

                if (!empty($validValues)) {
                    $input = self::input();
                    foreach ($validValues as $key => $value) {
                        Arr::set($input, $key, $value);
                    }
                    $validator = $this->request->createValidatorInstance($input);

                    $actual = $validator->passes();

                    $this->assertSame([], $validator->errors()->toArray());
                    $this->assertTrue($actual);
                }
            },
            ['examples' => $this->failedValidationExamples()]
        );
    }

    /**
     * リクエストクラスが受け取る入力のデフォルト値.
     *
     * @param array $overwrites
     * @return array
     */
    private static function input(array $overwrites = []): array
    {
        return [
            'effectivatedOn' => '2022-04-01',
            'status' => DwsCertificationStatus::approved()->value(),
            'dwsNumber' => '0001287325',
            'dwsTypes' => [
                DwsType::physical()->value(),
                DwsType::intractableDiseases()->value(),
            ],
            'issuedOn' => '2022-04-01',
            'cityName' => '中野区',
            'cityCode' => '131148',
            'dwsLevel' => DwsLevel::level6()->value(),
            'isSubjectOfComprehensiveSupport' => true,
            'activatedOn' => '2021-04-01',
            'deactivatedOn' => '2024-03-31',
            'grants' => [
                [
                    'dwsCertificationServiceType' => DwsCertificationServiceType::physicalCare()->value(),
                    'grantedAmount' => '999時間/月',
                    'activatedOn' => '2022-01-01',
                    'deactivatedOn' => '2022-12-31',
                ],
            ],
            'child' => [
                'name' => [
                    'familyName' => '内藤',
                    'givenName' => '勇介',
                    'phoneticFamilyName' => 'ナイトウ',
                    'phoneticGivenName' => 'ユウスケ',
                ],
                'birthday' => '1985-02-24',
            ],
            'copayLimit' => 37200,
            'copayActivatedOn' => '2022-04-01',
            'copayDeactivatedOn' => '2023-03-31',
            'copayCoordination' => [
                'copayCoordinationType' => CopayCoordinationType::internal()->value(),
                'officeId' => 13,
            ],
            'agreements' => [
                [
                    'indexNumber' => '1',
                    'officeId' => 13,
                    'dwsCertificationAgreementType' => DwsCertificationAgreementType::physicalCare()->value(),
                    'paymentAmount' => 6000, // 100時間（6,000分）
                    'agreedOn' => '2021-09-01',
                    'expiredOn' => '2023-01-31',
                ],
            ],
            ...$overwrites,
        ];
    }

    /**
     * バリデーションを通過するパターン.
     *
     * @return array[]
     */
    private function passedValidationExamples(): array
    {
        return [
            'すべての項目に入力がある場合' => [[]],
            '上限管理区分が「上限管理なし」で上限管理事業者が未入力の場合' => [
                ['copayCoordination' => ['copayCoordinationType' => CopayCoordinationType::none()->value()]],
            ],
        ];
    }

    /**
     * バリデーションを通過しないパターンを生成する.
     *
     * @param array $invalidInput
     * @param array $expectedErrors
     * @param array $validInput
     * @param null|\Closure $postulate
     * @return array
     */
    private static function failedPattern(
        ?Closure $postulate = null,
        array $validInput = [],
        array $invalidInput = [],
        array $expectedErrors = []
    ): array {
        return [$invalidInput, $validInput, $expectedErrors, $postulate];
    }

    /**
     * バリデーションを通過しないパターン.
     *
     * @return array[]
     */
    private function failedValidationExamples(): array
    {
        return [
            '適用日が未入力の場合' => self::failedPattern(
                invalidInput: ['effectivatedOn' => ''],
                expectedErrors: ['effectivatedOn' => ['入力してください。']],
            ),
            '適用日が日付形式の文字列でない場合' => self::failedPattern(
                invalidInput: ['effectivatedOn' => 'THIS IS NOT A DATE'],
                expectedErrors: ['effectivatedOn' => ['正しい日付を入力してください。']],
            ),
            '適用日が存在しない日付である場合' => self::failedPattern(
                validInput: ['effectivatedOn' => '1999-02-28'],
                invalidInput: ['effectivatedOn' => '1999-02-29'],
                expectedErrors: ['effectivatedOn' => ['正しい日付を入力してください。']],
            ),
            '認定区分が未入力の場合' => self::failedPattern(
                invalidInput: ['status' => ''],
                expectedErrors: ['status' => ['入力してください。']],
            ),
            '認定区分に未定義の値が入力された場合' => self::failedPattern(
                invalidInput: ['status' => -1],
                expectedErrors: ['status' => ['障害福祉サービス受給者証 認定区分を指定してください。']],
            ),
            '受給者証番号が未入力の場合' => self::failedPattern(
                invalidInput: ['dwsNumber' => ''],
                expectedErrors: ['dwsNumber' => ['入力してください。']],
            ),
            '受給者証番号の桁数が10桁未満の場合' => self::failedPattern(
                invalidInput: ['dwsNumber' => '123456789'],
                expectedErrors: ['dwsNumber' => ['10桁で入力してください。']],
            ),
            '受給者証番号の桁数が10桁を超える場合' => self::failedPattern(
                invalidInput: ['dwsNumber' => '12345678900'],
                expectedErrors: ['dwsNumber' => ['10桁で入力してください。']],
            ),
            '受給者証番号に数字以外が含まれる場合' => self::failedPattern(
                invalidInput: ['dwsNumber' => '123456789A'],
                expectedErrors: ['dwsNumber' => ['10桁で入力してください。']],
            ),
            '障害種別が未入力（空の配列）の場合' => self::failedPattern(
                invalidInput: ['dwsTypes' => []],
                expectedErrors: ['dwsTypes' => ['入力してください。']],
            ),
            '障害種別が配列でない場合' => self::failedPattern(
                invalidInput: ['dwsTypes' => 'THIS IS NOT AN ARRAY'],
                expectedErrors: ['dwsTypes' => ['配列にしてください。']],
            ),
            '障害種別が未入力（配列の要素が空文字）の場合' => self::failedPattern(
                invalidInput: ['dwsTypes' => ['']],
                expectedErrors: ['dwsTypes.0' => ['入力してください。']],
            ),
            '障害種別に未定義の値が入力された場合' => self::failedPattern(
                invalidInput: ['dwsTypes.0' => -1],
                expectedErrors: ['dwsTypes.0' => ['障害種別を指定してください。']],
            ),
            '交付日が未入力の場合' => self::failedPattern(
                invalidInput: ['issuedOn' => ''],
                expectedErrors: ['issuedOn' => ['入力してください。']],
            ),
            '交付日が日付形式の文字列でない場合' => self::failedPattern(
                invalidInput: ['issuedOn' => 'THIS IS NOT A DATE'],
                expectedErrors: ['issuedOn' => ['正しい日付を入力してください。']],
            ),
            '交付日が存在しない日付である場合' => self::failedPattern(
                validInput: ['issuedOn' => '1999-02-28'],
                invalidInput: ['issuedOn' => '1999-02-29'],
                expectedErrors: ['issuedOn' => ['正しい日付を入力してください。']],
            ),
            '市町村名が未入力の場合' => self::failedPattern(
                invalidInput: ['cityName' => ''],
                expectedErrors: ['cityName' => ['入力してください。']],
            ),
            '市町村名が100文字を超える場合' => self::failedPattern(
                validInput: ['cityName' => str_repeat('山', 100)],
                invalidInput: ['cityName' => str_repeat('山', 101)],
                expectedErrors: ['cityName' => ['100文字以内で入力してください。']],
            ),
            '市町村番号が未入力の場合' => self::failedPattern(
                invalidInput: ['cityCode' => ''],
                expectedErrors: ['cityCode' => ['入力してください。']],
            ),
            '市町村番号が6桁未満の場合' => self::failedPattern(
                validInput: ['cityCode' => str_repeat('1', 6)],
                invalidInput: ['cityCode' => str_repeat('1', 5)],
                expectedErrors: ['cityCode' => ['6桁で入力してください。']],
            ),
            '市町村番号が6桁を超える場合' => self::failedPattern(
                validInput: ['cityCode' => str_repeat('1', 6)],
                invalidInput: ['cityCode' => str_repeat('山', 7)],
                expectedErrors: ['cityCode' => ['6桁で入力してください。']],
            ),
            '障害支援区分が未入力の場合' => self::failedPattern(
                invalidInput: ['dwsLevel' => ''],
                expectedErrors: ['dwsLevel' => ['入力してください。']],
            ),
            '障害支援区分に未定義の値が入力された場合' => self::failedPattern(
                invalidInput: ['dwsLevel' => -1],
                expectedErrors: ['dwsLevel' => ['障害程度区分を指定してください。']],
            ),
            '重度障害者等包括支援対象（フラグ）が未入力の場合' => self::failedPattern(
                invalidInput: ['isSubjectOfComprehensiveSupport' => ''],
                expectedErrors: ['isSubjectOfComprehensiveSupport' => ['入力してください。']],
            ),
            '重度障害者等包括支援対象（フラグ）が真偽値でない場合' => self::failedPattern(
                validInput: ['isSubjectOfComprehensiveSupport' => 1],
                invalidInput: ['isSubjectOfComprehensiveSupport' => 2],
                expectedErrors: ['isSubjectOfComprehensiveSupport' => ['trueかfalseにしてください。']],
            ),
            '認定の有効期間（開始）が未入力の場合' => self::failedPattern(
                invalidInput: ['activatedOn' => ''],
                expectedErrors: ['activatedOn' => ['入力してください。']],
            ),
            '認定の有効期間（開始）が日付形式の文字列でない場合' => self::failedPattern(
                invalidInput: ['activatedOn' => 'THIS IS NOT A DATE'],
                expectedErrors: ['activatedOn' => ['正しい日付を入力してください。']],
            ),
            '認定の有効期間（開始）が存在しない日付である場合' => self::failedPattern(
                validInput: ['activatedOn' => '1999-02-28'],
                invalidInput: ['activatedOn' => '1999-02-29'],
                expectedErrors: ['activatedOn' => ['正しい日付を入力してください。']],
            ),
            '認定の有効期間（終了）が未入力の場合' => self::failedPattern(
                invalidInput: ['deactivatedOn' => ''],
                expectedErrors: ['deactivatedOn' => ['入力してください。']],
            ),
            '認定の有効期間（終了）が日付形式の文字列でない場合' => self::failedPattern(
                invalidInput: ['deactivatedOn' => 'THIS IS NOT A DATE'],
                expectedErrors: ['deactivatedOn' => ['正しい日付を入力してください。']],
            ),
            '認定の有効期間（終了）が存在しない日付である場合' => self::failedPattern(
                validInput: ['deactivatedOn' => '1999-02-28'],
                invalidInput: ['deactivatedOn' => '1999-02-29'],
                expectedErrors: ['deactivatedOn' => ['正しい日付を入力してください。']],
            ),
            '介護給付費の支給決定内容が未入力（空の配列）の場合' => self::failedPattern(
                invalidInput: ['grants' => []],
                expectedErrors: ['grants' => ['入力してください。']],
            ),
            '介護給付費の支給決定内容が配列でない場合' => self::failedPattern(
                invalidInput: ['grants' => 'THIS IS NOT AN ARRAY'],
                expectedErrors: ['grants' => ['配列にしてください。']],
            ),
            '介護給付費の支給決定内容「サービス種別」と障害支援区分に矛盾がある場合' => self::failedPattern(
                validInput: [
                    'dwsLevel' => DwsLevel::level6()->value(),
                    'grants.0.dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd2()->value(),
                ],
                invalidInput: [
                    'dwsLevel' => DwsLevel::level5()->value(),
                    'grants.0.dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd2()->value(),
                ],
                expectedErrors: [
                    'grants.0' => ['障害支援区分と矛盾するサービス種別です。間違いがないかご確認ください。'],
                ],
            ),
            '重度訪問介護の支給決定期間が重複している場合' => self::failedPattern(
                invalidInput: [
                    'grants' => [
                        [
                            'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd1()->value(),
                            'grantedAmount' => $this->examples->dwsCertifications[0]->grants[0]->grantedAmount,
                            'activatedOn' => '2020-01-01',
                            'deactivatedOn' => '2020-12-01',
                        ],
                        [
                            'dwsCertificationServiceType' => DwsCertificationServiceType::visitingCareForPwsd2()->value(),
                            'grantedAmount' => $this->examples->dwsCertifications[0]->grants[0]->grantedAmount,
                            'activatedOn' => '2020-01-01',
                            'deactivatedOn' => '2020-12-01',
                        ],
                    ],
                ],
                expectedErrors: [
                    'grants.0' => ['支給決定期間が重複する重度訪問介護の支給決定内容が他に存在します。'],
                    'grants.1' => ['支給決定期間が重複する重度訪問介護の支給決定内容が他に存在します。'],
                ],
            ),
            '介護給付費の支給決定内容「サービス種別」が未入力の場合' => self::failedPattern(
                invalidInput: ['grants.0.dwsCertificationServiceType' => ''],
                expectedErrors: ['grants.0.dwsCertificationServiceType' => ['入力してください。']],
            ),
            '介護給付費の支給決定内容「サービス種別」に未定義の値が入力された場合' => self::failedPattern(
                invalidInput: ['grants.0.dwsCertificationServiceType' => -1],
                expectedErrors: [
                    'grants.0.dwsCertificationServiceType' => [
                        '障害福祉サービス受給者証 サービス種別を指定してください。',
                    ],
                ],
            ),
            '介護給付費の支給決定内容「支給量等」が未入力の場合' => self::failedPattern(
                invalidInput: ['grants.0.grantedAmount' => ''],
                expectedErrors: ['grants.0.grantedAmount' => ['入力してください。']],
            ),
            '介護給付費の支給決定内容「支給量等」が255文字を超える場合' => self::failedPattern(
                validInput: ['grants.0.grantedAmount' => str_repeat('山', 255)],
                invalidInput: ['grants.0.grantedAmount' => str_repeat('山', 256)],
                expectedErrors: ['grants.0.grantedAmount' => ['255文字以内で入力してください。']],
            ),
            '介護給付費の支給決定内容「支給決定期間」の開始日が未入力の場合' => self::failedPattern(
                invalidInput: ['grants.0.activatedOn' => ''],
                expectedErrors: ['grants.0.activatedOn' => ['入力してください。']],
            ),
            '介護給付費の支給決定内容「支給決定期間」の開始日が日付形式の文字列でない場合' => self::failedPattern(
                invalidInput: ['grants.0.activatedOn' => 'THIS IS NOT A DATE'],
                expectedErrors: ['grants.0.activatedOn' => ['正しい日付を入力してください。']],
            ),
            '介護給付費の支給決定内容「支給決定期間」の開始日が存在しない日付である場合' => self::failedPattern(
                validInput: ['grants.0.activatedOn' => '1999-02-28'],
                invalidInput: ['grants.0.activatedOn' => '1999-02-29'],
                expectedErrors: ['grants.0.activatedOn' => ['正しい日付を入力してください。']],
            ),
            '介護給付費の支給決定内容「支給決定期間」の終了日が未入力の場合' => self::failedPattern(
                invalidInput: ['grants.0.deactivatedOn' => ''],
                expectedErrors: ['grants.0.deactivatedOn' => ['入力してください。']],
            ),
            '介護給付費の支給決定内容「支給決定期間」の終了日が日付形式の文字列でない場合' => self::failedPattern(
                invalidInput: ['grants.0.deactivatedOn' => 'THIS IS NOT A DATE'],
                expectedErrors: ['grants.0.deactivatedOn' => ['正しい日付を入力してください。']],
            ),
            '介護給付費の支給決定内容「支給決定期間」の終了日が存在しない日付である場合' => self::failedPattern(
                validInput: ['grants.0.deactivatedOn' => '1999-02-28'],
                invalidInput: ['grants.0.deactivatedOn' => '1999-02-29'],
                expectedErrors: ['grants.0.deactivatedOn' => ['正しい日付を入力してください。']],
            ),
            '児童情報「氏名」の姓が100文字を超える場合' => self::failedPattern(
                validInput: ['child.name.familyName' => str_repeat('山', 100)],
                invalidInput: ['child.name.familyName' => str_repeat('山', 101)],
                expectedErrors: ['child.name.familyName' => ['100文字以内で入力してください。']],
            ),
            '児童情報「氏名」の名が100文字を超える場合' => self::failedPattern(
                validInput: ['child.name.givenName' => str_repeat('山', 100)],
                invalidInput: ['child.name.givenName' => str_repeat('山', 101)],
                expectedErrors: ['child.name.givenName' => ['100文字以内で入力してください。']],
            ),
            '児童情報「氏名」の姓（フリガナ）にカタカナ以外の文字が含まれる場合' => self::failedPattern(
                validInput: ['child.name.phoneticFamilyName' => 'サエモンサブロウ'],
                invalidInput: ['child.name.phoneticFamilyName' => '左衛門サブロウ'],
                expectedErrors: ['child.name.phoneticFamilyName' => ['カタカナで入力してください。']],
            ),
            '児童情報「氏名」の姓（フリガナ）が100文字を超える場合' => self::failedPattern(
                validInput: ['child.name.phoneticFamilyName' => str_repeat('ア', 100)],
                invalidInput: ['child.name.phoneticFamilyName' => str_repeat('ア', 101)],
                expectedErrors: ['child.name.phoneticFamilyName' => ['100文字以内で入力してください。']],
            ),
            '児童情報「氏名」の名（フリガナ）にカタカナ以外の文字が含まれる場合' => self::failedPattern(
                validInput: ['child.name.phoneticGivenName' => 'ユースケ'],
                invalidInput: ['child.name.phoneticGivenName' => '勇介サンタマリア'],
                expectedErrors: ['child.name.phoneticGivenName' => ['カタカナで入力してください。']],
            ),
            '児童情報「氏名」の名（フリガナ）が100文字を超える場合' => self::failedPattern(
                validInput: ['child.name.phoneticGivenName' => str_repeat('ア', 100)],
                invalidInput: ['child.name.phoneticGivenName' => str_repeat('ア', 101)],
                expectedErrors: ['child.name.phoneticGivenName' => ['100文字以内で入力してください。']],
            ),
            '児童情報「生年月日」が日付形式の文字列でない場合' => self::failedPattern(
                invalidInput: ['child.birthday' => 'THIS IS NOT A DATE'],
                expectedErrors: ['child.birthday' => ['正しい日付を入力してください。']],
            ),
            '児童情報「生年月日」が存在しない日付である場合' => self::failedPattern(
                validInput: ['child.birthday' => '1999-02-28'],
                invalidInput: ['child.birthday' => '1999-02-29'],
                expectedErrors: ['child.birthday' => ['正しい日付を入力してください。']],
            ),
            '利用者負担上限月額が未入力の場合' => self::failedPattern(
                invalidInput: ['copayLimit' => ''],
                expectedErrors: ['copayLimit' => ['入力してください。']],
            ),
            '利用者負担上限月額に整数以外の値が入力された場合' => self::failedPattern(
                invalidInput: ['copayLimit' => 3.14],
                expectedErrors: ['copayLimit' => ['整数で入力してください。']],
            ),
            '利用者負担適用期間（開始）が未入力の場合' => self::failedPattern(
                invalidInput: ['copayActivatedOn' => ''],
                expectedErrors: ['copayActivatedOn' => ['入力してください。']],
            ),
            '利用者負担適用期間（開始）が日付形式の文字列でない場合' => self::failedPattern(
                invalidInput: ['copayActivatedOn' => 'THIS IS NOT A DATE'],
                expectedErrors: ['copayActivatedOn' => ['正しい日付を入力してください。']],
            ),
            '利用者負担適用期間（開始）が存在しない日付である場合' => self::failedPattern(
                validInput: ['copayActivatedOn' => '1999-02-28'],
                invalidInput: ['copayActivatedOn' => '1999-02-29'],
                expectedErrors: ['copayActivatedOn' => ['正しい日付を入力してください。']],
            ),
            '利用者負担適用期間（終了）が未入力の場合' => self::failedPattern(
                invalidInput: ['copayDeactivatedOn' => ''],
                expectedErrors: ['copayDeactivatedOn' => ['入力してください。']],
            ),
            '利用者負担適用期間（終了）が日付形式の文字列でない場合' => self::failedPattern(
                invalidInput: ['copayDeactivatedOn' => 'THIS IS NOT A DATE'],
                expectedErrors: ['copayDeactivatedOn' => ['正しい日付を入力してください。']],
            ),
            '利用者負担適用期間（終了）が存在しない日付である場合' => self::failedPattern(
                validInput: ['copayDeactivatedOn' => '1999-02-28'],
                invalidInput: ['copayDeactivatedOn' => '1999-02-29'],
                expectedErrors: ['copayDeactivatedOn' => ['正しい日付を入力してください。']],
            ),
            '上限管理区分が未入力の場合' => self::failedPattern(
                invalidInput: ['copayCoordination.copayCoordinationType' => ''],
                expectedErrors: ['copayCoordination.copayCoordinationType' => ['入力してください。']],
            ),
            '上限管理区分に未定義の値が入力された場合' => self::failedPattern(
                invalidInput: ['copayCoordination.copayCoordinationType' => -1],
                expectedErrors: ['copayCoordination.copayCoordinationType' => ['上限管理区分を指定してください。']],
            ),
            '上限管理区分が「自社事業所」で上限管理事業者が未入力の場合' => self::failedPattern(
                invalidInput: [
                    'copayCoordination.copayCoordinationType' => CopayCoordinationType::internal()->value(),
                    'copayCoordination.officeId' => null,
                ],
                expectedErrors: ['copayCoordination.officeId' => ['入力してください。']],
            ),
            '上限管理区分が「他社事業所」で上限管理事業者が未入力の場合' => self::failedPattern(
                invalidInput: [
                    'copayCoordination.copayCoordinationType' => CopayCoordinationType::external()->value(),
                    'copayCoordination.officeId' => null,
                ],
                expectedErrors: ['copayCoordination.officeId' => ['入力してください。']],
            ),
            '指定された上限管理事業所が存在しない場合' => self::failedPattern(
                postulate: function (): void {
                    $this->getOfficeListUseCase->allows('handle')->andReturn(Seq::empty());
                },
                invalidInput: [
                    'copayCoordination.copayCoordinationType' => CopayCoordinationType::internal()->value(),
                    'copayCoordination.officeId' => 13,
                ],
                expectedErrors: ['copayCoordination.officeId' => ['正しい値を入力してください。']],
            ),
            '訪問系サービス事業者記入欄が未入力（空の配列）の場合' => self::failedPattern(
                invalidInput: ['agreements' => []],
                expectedErrors: ['agreements' => ['入力してください。']],
            ),
            '訪問系サービス事業者記入欄が配列でない場合' => self::failedPattern(
                invalidInput: ['agreements' => 'THIS IS NOT AN ARRAY'],
                expectedErrors: ['agreements' => ['配列にしてください。']],
            ),
            '訪問系サービス事業者記入欄「番号」が未入力の場合' => self::failedPattern(
                invalidInput: ['agreements.0.indexNumber' => ''],
                expectedErrors: ['agreements.0.indexNumber' => ['入力してください。']],
            ),
            '訪問系サービス事業者記入欄「番号」に整数以外の値が入力された場合' => self::failedPattern(
                invalidInput: ['agreements.0.indexNumber' => 3.14],
                expectedErrors: ['agreements.0.indexNumber' => ['整数で入力してください。']],
            ),
            '訪問系サービス事業者記入欄「事業所」が未入力の場合' => self::failedPattern(
                invalidInput: ['agreements.0.officeId' => null],
                expectedErrors: ['agreements.0.officeId' => ['入力してください。']],
            ),
            '訪問系サービス事業者記入欄「事業所」で指定された事業所が存在しない場合' => self::failedPattern(
                postulate: function (): void {
                    $this->lookupOfficeUseCase->allows('handle')->andReturn(Seq::empty());
                },
                invalidInput: ['agreements.0.officeId' => 13],
                expectedErrors: ['agreements.0.officeId' => ['正しい値を入力してください。']],
            ),
            '訪問系サービス事業者記入欄「サービス内容」が未入力の場合' => self::failedPattern(
                invalidInput: ['agreements.0.dwsCertificationAgreementType' => ''],
                expectedErrors: ['agreements.0.dwsCertificationAgreementType' => ['入力してください。']],
            ),
            '訪問系サービス事業者記入欄「サービス内容」に未定義の値が入力された場合' => self::failedPattern(
                invalidInput: ['agreements.0.dwsCertificationAgreementType' => -1],
                expectedErrors: [
                    'agreements.0.dwsCertificationAgreementType' => [
                        '障害福祉サービス受給者証 サービス内容を指定してください。',
                    ],
                ],
            ),
            '訪問系サービス事業者記入欄「サービス内容」と障害支援区分の組み合わせに矛盾がある場合' => self::failedPattern(
                validInput: [
                    'dwsLevel' => DwsLevel::level5()->value(),
                    'agreements.0.dwsCertificationAgreementType' => DwsCertificationAgreementType::visitingCareForPwsd3()->value(),
                ],
                invalidInput: [
                    'dwsLevel' => DwsLevel::level5()->value(),
                    'agreements.0.dwsCertificationAgreementType' => DwsCertificationAgreementType::visitingCareForPwsd2()->value(),
                ],
                expectedErrors: [
                    'agreements.0.dwsCertificationAgreementType' => [
                        '障害支援区分とサービス内容の組み合わせが正しくありません。間違いがないかご確認ください。',
                    ],
                ],
            ),
            '訪問系サービス事業者記入欄「契約支給量」が未入力の場合' => self::failedPattern(
                invalidInput: ['agreements.0.paymentAmount' => ''],
                expectedErrors: ['agreements.0.paymentAmount' => ['入力してください。']],
            ),
            '訪問系サービス事業者記入欄「契約支給量」に整数以外の値が入力された場合' => self::failedPattern(
                invalidInput: ['agreements.0.paymentAmount' => 3.14],
                expectedErrors: ['agreements.0.paymentAmount' => ['整数で入力してください。']],
            ),
            '訪問系サービス事業者記入欄「契約日」が未入力の場合' => self::failedPattern(
                invalidInput: ['agreements.0.agreedOn' => ''],
                expectedErrors: ['agreements.0.agreedOn' => ['入力してください。']],
            ),
            '訪問系サービス事業者記入欄「契約日」が日付形式の文字列でない場合' => self::failedPattern(
                invalidInput: ['agreements.0.agreedOn' => 'THIS IS NOT A DATE'],
                expectedErrors: ['agreements.0.agreedOn' => ['正しい日付を入力してください。']],
            ),
            '訪問系サービス事業者記入欄「契約日」が存在しない日付である場合' => self::failedPattern(
                validInput: ['agreements.0.agreedOn' => '1999-02-28'],
                invalidInput: ['agreements.0.agreedOn' => '1999-02-29'],
                expectedErrors: ['agreements.0.agreedOn' => ['正しい日付を入力してください。']],
            ),
            '訪問系サービス事業者記入欄「当該契約支給量によるサービス提供終了日」が日付形式の文字列でない場合' => self::failedPattern(
                invalidInput: ['agreements.0.expiredOn' => 'THIS IS NOT A DATE'],
                expectedErrors: ['agreements.0.expiredOn' => ['正しい日付を入力してください。']],
            ),
            '訪問系サービス事業者記入欄「当該契約支給量によるサービス提供終了日」が存在しない日付である場合' => self::failedPattern(
                validInput: ['agreements.0.expiredOn' => '1999-02-28'],
                invalidInput: ['agreements.0.expiredOn' => '1999-02-29'],
                expectedErrors: ['agreements.0.expiredOn' => ['正しい日付を入力してください。']],
            ),
        ];
    }
}
