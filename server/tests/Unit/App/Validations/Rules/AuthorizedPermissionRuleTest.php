<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Validations\Rules;

use App\Validations\CustomValidator;
use Domain\Permission\Permission;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ContextMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Test;

/**
 * {@link \App\Validations\Rules\AuthorizedPermissionRule} のテスト.
 */
final class AuthorizedPermissionRuleTest extends Test
{
    use ContextMixin;
    use ExamplesConsumer;
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
    public function describe_validateAuthorizedPermission(): void
    {
        $this->should('pass when specify authorized permission', function (): void {
            $this->context
                ->expects('isAuthorizedTo')
                ->with(Permission::listUsers())
                ->andReturn(true);

            $this->assertTrue(
                $this->authorizedPermissionValidator(Permission::listUsers()->value())->passes()
            );
        });
        $this->should('pass when specify invalid permission', function (): void {
            $this->assertTrue(
                $this->authorizedPermissionValidator((string)self::INVALID_ENUM_VALUE)->passes()
            );
        });
        $this->should('fail when specify authorized permission', function (): void {
            $this->context
                ->allows('isAuthorizedTo')
                ->with(Permission::listUsers())
                ->andReturn(true);
            $this->context
                ->expects('isAuthorizedTo')
                ->with(Permission::createUsers())
                ->andReturn(false);

            $this->assertTrue(
                $this->authorizedPermissionValidator(Permission::createUsers()->value())->fails()
            );
        });
    }

    /**
     * 「権限」をもっていることを検証するバリデータを生成する.
     *
     * @param string $permission
     * @return CustomValidator
     */
    private function authorizedPermissionValidator(string $permission): CustomValidator
    {
        return $this->buildCustomValidator(
            ['permission' => $permission],
            ['permission' => 'authorized_permission'],
        );
    }
}
