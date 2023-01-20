<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http;

use App\Http\HttpContext;
use Domain\Permission\Permission;
use Domain\Role\RoleScope;
use Lib\Exceptions\InvalidArgumentException;
use ScalikePHP\Option;
use ScalikePHP\Seq;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Test;

/**
 * {@link \App\Http\HttpContext} Test.
 */
class HttpContextTest extends Test
{
    use ExamplesConsumer;
    use UnitSupport;

    public const URI = '';

    private Permission $permission;
    private HttpContext $context;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (HttpContextTest $self): void {
            $self->permission = Permission::listInternalOffices();
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_isAccessibleTo(): void
    {
        $this->should('return false when different organization', function (): void {
            $this->context = new HttpContext(
                $this->examples->organizations[0],
                Option::from($this->examples->staffs[0]),
                Seq::from($this->examples->roles[0]),
                self::URI,
                Seq::fromArray([$this->examples->offices[0]]),
                Seq::emptySeq()
            );
            $this->assertFalse($this->context->isAccessibleTo($this->permission, $this->examples->organizations[2]->id, [$this->examples->offices[0]]));
        });
        $this->should('return true when Context is SystemAdmin', function (): void {
            $this->context = new HttpContext(
                $this->examples->organizations[0],
                Option::from($this->examples->staffs[0]),
                Seq::from($this->examples->roles[0]),
                self::URI,
                Seq::fromArray([$this->examples->offices[0]]),
                Seq::emptySeq()
            );
            $this->assertTrue($this->context->isAccessibleTo($this->permission, $this->examples->organizations[0]->id, [$this->examples->offices[0]]));
        });
        $this->should('return true when RoleScopes that Roles in specified Permission is Whole', function (): void {
            $role = $this->examples->roles[4];
            $this->context = new HttpContext(
                $this->examples->organizations[0],
                Option::from($this->examples->staffs[0]),
                Seq::from($role),
                self::URI,
                Seq::fromArray([$this->examples->offices[0]]),
                Seq::emptySeq()
            );
            $this->assertTrue($this->context->isAccessibleTo($role->permissions[0], $this->examples->organizations[0]->id, [$this->examples->offices[0]]));
        });
        $this->should('return true when RoleScope that Roles in specified Permission is Group', function (): void {
            $role = $this->examples->roles[6];
            $this->context = new HttpContext(
                $this->examples->organizations[0],
                Option::from($this->examples->staffs[0]),
                Seq::from($role),
                self::URI,
                Seq::fromArray([$this->examples->offices[0]]),
                Seq::fromArray([$this->examples->offices[1]]),
            );
            $this->assertTrue($this->context->isAccessibleTo(
                $role->permissions[0],
                $this->examples->organizations[0]->id,
                [$this->examples->offices[1]->id]
            ));
        });
        $this->should('return false when RoleScope that Roles in specified Permission is Group and accessed Office is not in GroupOffice', function (): void {
            $role = $this->examples->roles[6];
            $this->context = new HttpContext(
                $this->examples->organizations[0],
                Option::from($this->examples->staffs[0]),
                Seq::from($role),
                self::URI,
                Seq::fromArray([$this->examples->offices[0]]),
                Seq::fromArray([$this->examples->offices[1]]),
            );
            $this->assertFalse($this->context->isAccessibleTo(
                $role->permissions[0],
                $this->examples->organizations[0]->id,
                [$this->examples->offices[0]->id]
            ));
        });
        $this->should(
            'succeed normaly when `officeIds` is empty',
            function (RoleScope $scope, bool $expected): void {
                $role = $this->examples->roles[6]->copy(['scope' => $scope]);
                $this->context = new HttpContext(
                    $this->examples->organizations[0],
                    Option::from($this->examples->staffs[0]),
                    Seq::from($role),
                    self::URI,
                    Seq::fromArray([$this->examples->offices[0]]),
                    Seq::fromArray([$this->examples->offices[1]]),
                );
                $actual = $this->context->isAccessibleTo(
                    $role->permissions[0],
                    $this->examples->organizations[0]->id,
                    [],
                    $this->examples->staffs[0]->id
                );
                $this->assertSame($expected, $actual);
            },
            [
                'examples' => [
                    'RoleScope is whole' => [RoleScope::whole(), true],
                    'RoleScope is group' => [RoleScope::group(), false],
                    'RoleScope is office' => [RoleScope::office(), false],
                    'RoleScope is person' => [RoleScope::person(), true],
                ],
            ]
        );
        $this->should('return true when RoleScope that Roles in specified Permission is Office', function (): void {
            $role = $this->examples->roles[8];
            $this->context = new HttpContext(
                $this->examples->organizations[0],
                Option::from($this->examples->staffs[0]),
                Seq::from($role),
                self::URI,
                Seq::fromArray([$this->examples->offices[0]]),
                Seq::emptySeq()
            );
            $this->assertTrue($this->context->isAccessibleTo(
                $role->permissions[0],
                $this->examples->organizations[0]->id,
                [$this->examples->offices[0]->id]
            ));
        });
        $this->should('return false when RoleScope that Roles in specified Permission is Office and accessed Office is not in Offices', function (): void {
            $role = $this->examples->roles[8];
            $this->context = new HttpContext(
                $this->examples->organizations[0],
                Option::from($this->examples->staffs[0]),
                Seq::from($role),
                self::URI,
                Seq::fromArray([$this->examples->offices[0]]),
                Seq::emptySeq()
            );
            $this->assertFalse(
                $this->context->isAccessibleTo(
                    $role->permissions[0],
                    $this->examples->organizations[1]->id,
                    [$this->examples->offices[0]->id]
                )
            );
        });
        $this->should('return true when RoleScope that Roles in specified Permission is Person and accessed Staff is myself', function (): void {
            $role = $this->examples->roles[11];
            $this->context = new HttpContext(
                $this->examples->organizations[0],
                Option::from($this->examples->staffs[0]),
                Seq::from($role),
                self::URI,
                Seq::fromArray([$this->examples->offices[0]]),
                Seq::emptySeq(),
            );
            $this->assertTrue(
                $this->context->isAccessibleTo(
                    $role->permissions[0],
                    $this->examples->organizations[0]->id,
                    [$this->examples->offices[0]->id],
                    $this->examples->staffs[0]->id,
                ),
            );
        });
        $this->should('return false when RoleScope that Roles in specified Permission is Person and accessed Staff is not myself', function (): void {
            $role = $this->examples->roles[11];
            $this->context = new HttpContext(
                $this->examples->organizations[0],
                Option::from($this->examples->staffs[0]),
                Seq::from($role),
                self::URI,
                Seq::fromArray([$this->examples->offices[0]]),
                Seq::emptySeq(),
            );
            $this->assertFalse(
                $this->context->isAccessibleTo(
                    $role->permissions[0],
                    $this->examples->organizations[0]->id,
                    [$this->examples->offices[0]->id],
                    $this->examples->staffs[1]->id,
                ),
            );
        });
        $this->should('return false when RoleScope that Roles in specified Permission is Person and no accessed Staff', function (): void {
            $role = $this->examples->roles[11];
            $this->context = new HttpContext(
                $this->examples->organizations[0],
                Option::none(), // Staffなし
                Seq::from($role),
                self::URI,
                Seq::fromArray([$this->examples->offices[0]]),
                Seq::emptySeq(),
            );
            $this->assertFalse(
                $this->context->isAccessibleTo(
                    $role->permissions[0],
                    $this->examples->organizations[0]->id,
                    [$this->examples->offices[0]->id],
                    $this->examples->staffs[0]->id,
                ),
            );
        });
        $this->should('return true when a permission is specified and not contained', function (): void {
            $role = $this->examples->roles[6]->copy([
                'scope' => RoleScope::whole(),
                'permissions' => [
                    Permission::createUsers(),
                    Permission::updateUsers(),
                ],
            ]);
            $specifiedPermission = Permission::updateUsers();
            $this->context = new HttpContext(
                $this->examples->organizations[0],
                Option::from($this->examples->staffs[0]),
                Seq::from($role),
                self::URI,
                Seq::fromArray([$this->examples->offices[0]]),
                Seq::fromArray([$this->examples->offices[1]]),
            );

            $this->assertTrue($this->context->isAccessibleTo(
                $specifiedPermission,
                $this->examples->organizations[0]->id,
                [$this->examples->offices[1]->id]
            ));
        });
        $this->should('return false when a permission is specified and contained', function (): void {
            $role = $this->examples->roles[6]->copy([
                'scope' => RoleScope::whole(),
                'permissions' => [
                    Permission::createUsers(),
                    Permission::updateUsers(),
                ],
            ]);
            $specifiedPermission = Permission::deleteUsers();
            $this->context = new HttpContext(
                $this->examples->organizations[0],
                Option::from($this->examples->staffs[0]),
                Seq::from($role),
                self::URI,
                Seq::fromArray([$this->examples->offices[0]]),
                Seq::fromArray([$this->examples->offices[1]]),
            );

            $this->assertFalse($this->context->isAccessibleTo(
                $specifiedPermission,
                $this->examples->organizations[0]->id,
                [$this->examples->offices[1]->id]
            ));
        });
        $this->should('return true when multiple permissions are specified and any permissions are not contained', function (): void {
            $role = $this->examples->roles[6]->copy([
                'scope' => RoleScope::whole(),
                'permissions' => [
                    Permission::createUsers(),
                    Permission::updateUsers(),
                ],
            ]);
            $specifiedPermission = [
                Permission::listUsers(),
                Permission::viewUsers(),
                Permission::updateUsers(),
            ];
            $this->context = new HttpContext(
                $this->examples->organizations[0],
                Option::from($this->examples->staffs[0]),
                Seq::from($role),
                self::URI,
                Seq::fromArray([$this->examples->offices[0]]),
                Seq::fromArray([$this->examples->offices[1]]),
            );

            $this->assertTrue($this->context->isAccessibleTo(
                $specifiedPermission,
                $this->examples->organizations[0]->id,
                [$this->examples->offices[1]->id]
            ));
        });
        $this->should('return false when multiple permissions are specified and any permissions are contained', function (): void {
            $role = $this->examples->roles[6]->copy([
                'scope' => RoleScope::whole(),
                'permissions' => [
                    Permission::createUsers(),
                    Permission::updateUsers(),
                ],
            ]);
            $specifiedPermission = [
                Permission::listUsers(),
                Permission::viewUsers(),
                Permission::deleteUsers(),
            ];
            $this->context = new HttpContext(
                $this->examples->organizations[0],
                Option::from($this->examples->staffs[0]),
                Seq::from($role),
                self::URI,
                Seq::fromArray([$this->examples->offices[0]]),
                Seq::fromArray([$this->examples->offices[1]]),
            );

            $this->assertFalse($this->context->isAccessibleTo(
                $specifiedPermission,
                $this->examples->organizations[0]->id,
                [$this->examples->offices[1]->id]
            ));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_isAuthorizedTo(): void
    {
        $this->should('return true when role is system admin', function (): void {
            $this->context = new HttpContext(
                $this->examples->organizations[0],
                Option::from($this->examples->staffs[0]),
                Seq::from($this->examples->roles[0]),
                self::URI,
                Seq::fromArray([$this->examples->offices[0]]),
                Seq::emptySeq()
            );
            $this->assertTrue($this->context->isAuthorizedTo(Permission::listInternalOffices()));
        });
        $this->should('return true when Role have permission', function (): void {
            $role = $this->examples->roles[5];
            $this->context = new HttpContext(
                $this->examples->organizations[0],
                Option::from($this->examples->staffs[0]),
                Seq::from($role),
                self::URI,
                Seq::fromArray([$this->examples->offices[0]]),
                Seq::emptySeq()
            );
            $permissions = $role->permissions;

            $this->assertTrue($this->context->isAuthorizedTo(...$permissions));
        });
        $this->should('return false when Role not have permission', function (): void {
            $role = $this->examples->roles[5];
            $this->context = new HttpContext(
                $this->examples->organizations[0],
                Option::from($this->examples->staffs[0]),
                Seq::from($role),
                self::URI,
                Seq::fromArray([$this->examples->offices[0]]),
                Seq::emptySeq()
            );
            $permissions = Seq::fromArray(Permission::all())
                ->filter(fn (Permission $x): bool => !in_array($x, $role->permissions, true))
                ->toArray();

            $this->assertFalse($this->context->isAuthorizedTo(...$permissions));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_logContext(): void
    {
        $this->should('return array includes staffId', function (): void {
            $this->context = new HttpContext(
                $this->examples->organizations[0],
                Option::from($this->examples->staffs[0]),
                Seq::from($this->examples->roles[0]),
                self::URI,
                Seq::fromArray([$this->examples->offices[0]]),
                Seq::emptySeq()
            );
            $this->assertEquals(
                [
                    'organizationId' => $this->examples->organizations[0]->id,
                    'staffId' => $this->examples->staffs[0]->id,
                ],
                $this->context->logContext(),
            );
        });
        $this->should('return array not includes staffId', function (): void {
            $this->context = new HttpContext(
                $this->examples->organizations[0],
                Option::none(),
                Seq::from($this->examples->roles[0]),
                self::URI,
                Seq::fromArray([$this->examples->offices[0]]),
                Seq::emptySeq()
            );
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
    public function describe_hasRoleScope()
    {
        $this->should(
            'return true when isSystemAdmin is true',
            function ($role, $required): void {
                $this->context = new HttpContext(
                    $this->examples->organizations[0],
                    Option::none(),
                    Seq::from($role),
                    self::URI,
                    Seq::fromArray([$this->examples->offices[0]]),
                    Seq::emptySeq()
                );
                $this->assertTrue($this->context->hasRoleScope($required));
            },
            [
                'examples' => [
                    'isSystemAdmin is true' => [
                        'role' => $this->examples->roles[0],
                        'required' => RoleScope::whole(),
                    ],
                    'RoleScope is whole' => [
                        'role' => $this->examples->roles[4],
                        'required' => RoleScope::whole(),
                    ],
                    'RoleScope is group' => [
                        'role' => $this->examples->roles[6],
                        'required' => RoleScope::group(),
                    ],
                    'RoleScope is office' => [
                        'role' => $this->examples->roles[8],
                        'required' => RoleScope::office(),
                    ],
                    'RoleScope is person' => [
                        'role' => $this->examples->roles[11],
                        'required' => RoleScope::person(),
                    ],
                ],
            ]
        );
        $this->should('return false', function (): void {
            $this->context = new HttpContext(
                $this->examples->organizations[0],
                Option::none(),
                Seq::from($this->examples->roles[11]),
                self::URI,
                Seq::fromArray([$this->examples->offices[0]]),
                Seq::emptySeq()
            );
            $this->assertFalse($this->context->hasRoleScope(RoleScope::group()));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_uri(): void
    {
        $this->should('return string', function (): void {
            $this->context = new HttpContext(
                $this->examples->organizations[0],
                Option::none(),
                Seq::from($this->examples->roles[0]),
                self::URI,
                Seq::fromArray([$this->examples->offices[0]]),
                Seq::emptySeq()
            );
            $this->assertEquals(self::URI . 'path', $this->context->uri('path'));
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_isAuthenticated(): void
    {
        $this->should('return true when Staff is defined', function (): void {
            $this->context = new HttpContext(
                $this->examples->organizations[0],
                Option::from($this->examples->staffs[0]),
                Seq::from($this->examples->roles[0]),
                self::URI,
                Seq::fromArray([$this->examples->offices[0]]),
                Seq::emptySeq()
            );
            $this->assertTrue($this->context->isAuthenticated);
        });
        $this->should('return false when Staff is not defined', function (): void {
            $this->context = new HttpContext(
                $this->examples->organizations[0],
                Option::none(),
                Seq::from($this->examples->roles[0]),
                self::URI,
                Seq::fromArray([$this->examples->offices[0]]),
                Seq::emptySeq()
            );
            $this->assertFalse($this->context->isAuthenticated);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_organization(): void
    {
        $this->should('return string', function (): void {
            $this->context = new HttpContext(
                $this->examples->organizations[0],
                Option::from($this->examples->staffs[0]),
                Seq::from($this->examples->roles[0]),
                self::URI,
                Seq::fromArray([$this->examples->offices[0]]),
                Seq::emptySeq()
            );
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
            $this->context = new HttpContext(
                $this->examples->organizations[0],
                Option::from($this->examples->staffs[0]),
                Seq::from($this->examples->roles[0]),
                self::URI,
                Seq::fromArray([$this->examples->offices[0]]),
                Seq::emptySeq()
            );
            $this->assertEquals(Option::from($this->examples->staffs[0]), $this->context->staff);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_serializable(): void
    {
        $this->should('restore object', function (): void {
            $this->context = new HttpContext(
                $this->examples->organizations[0],
                Option::from($this->examples->staffs[0]),
                Seq::from($this->examples->roles[0]),
                self::URI,
                Seq::fromArray([$this->examples->offices[0]]),
                Seq::emptySeq()
            );
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
        $this->should('return none when this is SystemAdmin', function (): void {
            $context = new HttpContext(
                $this->examples->organizations[0],
                Option::from($this->examples->staffs[0]),
                Seq::from($this->examples->roles[0]),
                self::URI,
                Seq::fromArray([$this->examples->offices[0]]),
                Seq::emptySeq()
            );

            $this->assertEmpty($context->getPermittedOffices(Permission::viewInternalOffices()));
        });
        $this->should('return none when this RoleScope is Whole', function (): void {
            $context = new HttpContext(
                $this->examples->organizations[0],
                Option::from($this->examples->staffs[0]),
                Seq::from($this->examples->roles[4]),
                self::URI,
                Seq::fromArray([$this->examples->offices[0]]),
                Seq::emptySeq()
            );

            $this->assertEmpty($context->getPermittedOffices(Permission::viewStaffs()));
        });
        $this->should('return GroupsOffices when this RoleScope is Group', function (): void {
            $context = new HttpContext(
                $this->examples->organizations[0],
                Option::from($this->examples->staffs[0]),
                Seq::from($this->examples->roles[6]),
                self::URI,
                Seq::fromArray([$this->examples->offices[0]]),
                Seq::fromArray($this->examples->offices),
            );

            $actual = $context->getPermittedOffices(Permission::viewInternalOffices());
            $this->assertNotEmpty($actual);
            $this->assertArrayStrictEquals($this->examples->offices, $actual->head()->toArray());
        });
        $this->should('return Offices when this RoleScope is Office', function (): void {
            $context = new HttpContext(
                $this->examples->organizations[0],
                Option::from($this->examples->staffs[0]),
                Seq::from($this->examples->roles[8]),
                self::URI,
                Seq::from($this->examples->offices[1], $this->examples->offices[2]),
                Seq::emptySeq()
            );

            $actual = $context->getPermittedOffices(Permission::viewInternalOffices());
            $this->assertNotEmpty($actual);
            $this->assertArrayStrictEquals(
                [$this->examples->offices[1], $this->examples->offices[2]],
                $actual->head()->toArray()
            );
        });
        $this->should('return Offices when this RoleScope is Staff', function (): void {
            $context = new HttpContext(
                $this->examples->organizations[0],
                Option::from($this->examples->staffs[0]),
                Seq::from($this->examples->roles[11]),
                self::URI,
                Seq::from($this->examples->offices[2]),
                Seq::emptySeq()
            );

            $actual = $context->getPermittedOffices(Permission::viewStaffs());
            $this->assertNotEmpty($actual);
            $this->assertArrayStrictEquals(
                [$this->examples->offices[2]],
                $actual->head()->toArray()
            );
        });
        $this->should('throw exception when context has no specified permission', function (): void {
            $context = new HttpContext(
                $this->examples->organizations[0],
                Option::from($this->examples->staffs[0]),
                Seq::from($this->examples->roles[11]),
                self::URI,
                Seq::from($this->examples->offices[3]),
                Seq::emptySeq()
            );

            $this->assertThrows(
                InvalidArgumentException::class,
                function () use ($context): void {
                    $context->getPermittedOffices(Permission::viewInternalOffices());
                }
            );
        });
    }
}
