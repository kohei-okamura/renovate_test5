<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\CreateStaffPasswordResetRequest;
use App\Http\Requests\OrganizationRequest;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Test;

/**
 * CreateStaffPasswordResetRequest のテスト
 */
class CreateStaffPasswordResetRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use UnitSupport;

    public const DEFAULT_INPUT = [
        'email' => 'sample@example.com',
    ];

    protected CreateStaffPasswordResetRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (CreateStaffPasswordResetRequestTest $self): void {
            $self->request = new CreateStaffPasswordResetRequest();
            OrganizationRequest::prepareOrganizationRequest($self->request, $self->examples->organizations[0]);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validation(): void
    {
        $this->should('succeed', function (): void {
            $validator = $this->request->createValidatorInstance(self::DEFAULT_INPUT);

            $this->assertTrue($validator->passes());
        });
        $examples = [
            'when email is empty' => [
                ['email' => ['入力してください。']],
                ['email' => ''],
            ],
            'when email is longer than 255' => [
                ['email' => ['255文字以内で入力してください。']],
                ['email' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa@aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.example.com'],
                ['email' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa@aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.example.com'],
            ],
            'when email is not valid' => [
                ['email' => ['正しいメールアドレスで入力してください。']],
                ['email' => 'abcdefg'],
                ['email' => 'sample@example.com'],
            ],
        ];
        $this->should(
            'fail',
            function ($expected, $invalid, $valid = null): void {
                $validator = $this->request->createValidatorInstance($invalid + self::DEFAULT_INPUT);

                $this->assertTrue($validator->fails());
                $this->assertSame($validator->errors()->toArray(), $expected);
                if ($valid !== null) {
                    $validator = $this->request->createValidatorInstance($valid + self::DEFAULT_INPUT);
                    $this->assertTrue($validator->passes());
                }
            },
            compact('examples')
        );
    }
}
