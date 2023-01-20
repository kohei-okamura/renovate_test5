<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Requests;

use App\Http\Requests\AuthenticateStaffRequest;
use App\Http\Requests\OrganizationRequest;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OrganizationResolverMixin;
use Tests\Unit\Mixins\StaffResolverMixin;
use Tests\Unit\Test;

/**
 * AuthenticateStaffRequest のテスト.
 */
class AuthenticateStaffRequestTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use OrganizationResolverMixin;
    use StaffResolverMixin;
    use UnitSupport;

    protected AuthenticateStaffRequest $request;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (AuthenticateStaffRequestTest $self): void {
            $self->request = new AuthenticateStaffRequest();
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
            $validator = $this->request->createValidatorInstance($this->defaultInput());
            $this->assertTrue($validator->passes());
        });
        $examples = [
            'when email is empty' => [
                ['email' => ['入力してください。']],
                ['email' => ''],
            ],
            'when password is empty' => [
                ['password' => ['入力してください。']],
                ['password' => ''],
            ],
        ];
        $this->should(
            'fail',
            function ($expected, $invalid, $valid = null): void {
                $validator = $this->request->createValidatorInstance($invalid + $this->defaultInput());
                $this->assertTrue($validator->fails());
                $this->assertSame($validator->errors()->toArray(), $expected);
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
        return [
            'email' => 'sample@example.com',
            'password' => str_repeat('x', 8),
            'rememberMe' => true,
        ];
    }
}
