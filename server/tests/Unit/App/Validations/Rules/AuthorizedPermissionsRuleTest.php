<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use Lib\Exceptions\InvalidArgumentException;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\AuthorizedPermissionsRule} のテスト.
 */
final class AuthorizedPermissionsRuleTest extends Test
{
    use ContextMixin;
    use MatchesSnapshots;
    use MockeryMixin;
    use RuleTestSupport;
    use UnitSupport;

    /**
     * 初期化処理.
     */
    public static function _setUpSuite(): void
    {
        self::beforeEachTest(function (self $self): void {
        });
        self::beforeEachSpec(function (self $self): void {
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_validateAuthorizedPermissions(): void
    {
        $this->should('pass validation', function () {
            $this->context
                ->allows('isAuthorizedTo')
                ->andReturn(true);
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => ['offices/view' => true]],
                    ['value' => 'authorized_permissions']
                )->passes()
            );
        });
        $this->should('fail validation', function () {
            $this->context
                ->allows('isAuthorizedTo')
                ->andReturn(false);
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => ['offices/view' => true]],
                    ['value' => 'authorized_permissions']
                )->fails()
            );
        });
        $this->should('fail when throw Exception', function () {
            $this->context
                ->allows('isAuthorizedTo')
                ->andThrow(InvalidArgumentException::class);
            $this->assertTrue(
                $this->buildCustomValidator(
                    ['value' => ['offices/view' => true]],
                    ['value' => 'authorized_permissions']
                )->fails()
            );
        });
    }
}
