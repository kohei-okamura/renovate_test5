<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Console;

use App\Console\ConsoleContext;
use Domain\Permission\Permission;
use Domain\Role\RoleScope;
use ScalikePHP\Option;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \App\Console\ConsoleContext} Test.
 */
class ConsoleContextTest extends Test
{
    use ExamplesConsumer;
    use UnitSupport;

    public const URI = '';

    private ConsoleContext $context;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (ConsoleContextTest $self): void {
            $self->context = new ConsoleContext(
                $self->examples->organizations[0],
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_isAccessibleTo(): void
    {
        $this->should('return false when different organization', function (): void {
            $this->assertFalse($this->context->isAccessibleTo(Permission::listInternalOffices(), $this->examples->organizations[2]->id, [$this->examples->offices[0]]));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_isAuthorizedTo(): void
    {
        $this->should('always return true', function (): void {
            $this->assertTrue($this->context->isAuthorizedTo(Permission::listInternalOffices()));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_logContext(): void
    {
        $this->should('return array', function (): void {
            $this->assertEquals(
                [
                    'organizationId' => $this->examples->organizations[0]->id,
                    'staffId' => '',
                ],
                $this->context->logContext(),
            );
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_hasRoleScope(): void
    {
        $this->should('return true', function (): void {
            $this->assertTrue($this->context->hasRoleScope(RoleScope::group()));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_uri(): void
    {
        $this->should('return string', function (): void {
            $expected = ''; // TODO DEV-2312
            $this->assertEquals($expected, $this->context->uri('path'));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_isAuthenticated(): void
    {
        $this->should('always return true', function (): void {
            $this->assertTrue($this->context->isAuthenticated);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_organization(): void
    {
        $this->should('return string', function (): void {
            $this->assertEquals($this->examples->organizations[0], $this->context->organization);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_staff(): void
    {
        $this->should('return string', function (): void {
            $this->assertEquals(Option::none(), $this->context->staff);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_serializable(): void
    {
        $this->should('restore object', function (): void {
            $serialize = serialize($this->context);
            $restore = unserialize($serialize);

            $this->assertEquals($this->context, $restore);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_getPermittedOffices(): void
    {
        $this->should('return none', function (): void {
            $this->assertSame(Option::none(), $this->context->getPermittedOffices(Permission::viewInternalOffices()));
        });
    }
}
