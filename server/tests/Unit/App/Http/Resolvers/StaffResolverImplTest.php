<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\App\Http\Resolvers;

use App\Http\Requests\StaffRequest;
use App\Resolvers\StaffResolverImpl;
use Domain\Common\Carbon;
use Domain\Common\Pagination;
use Domain\FinderResult;
use Domain\Staff\Staff;
use Domain\Staff\StaffRememberToken;
use Lib\Json;
use ScalikePHP\None;
use ScalikePHP\Seq;
use ScalikePHP\Some;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\ConfigMixin;
use Tests\Unit\Mixins\MockeryMixin;
use Tests\Unit\Mixins\OfficeFinderMixin;
use Tests\Unit\Mixins\OfficeGroupFinderMixin;
use Tests\Unit\Mixins\OfficeGroupRepositoryMixin;
use Tests\Unit\Mixins\OfficeRepositoryMixin;
use Tests\Unit\Mixins\RoleRepositoryMixin;
use Tests\Unit\Mixins\SessionMixin;
use Tests\Unit\Mixins\StaffRememberTokenRepositoryMixin;
use Tests\Unit\Mixins\StaffRepositoryMixin;
use Tests\Unit\Test;

/**
 * StaffResolverImpl のテスト.
 */
final class StaffResolverImplTest extends Test
{
    use ConfigMixin;
    use ExamplesConsumer;
    use MockeryMixin;
    use OfficeFinderMixin;
    use OfficeRepositoryMixin;
    use OfficeGroupFinderMixin;
    use OfficeGroupRepositoryMixin;
    use OfficeRepositoryMixin;
    use RoleRepositoryMixin;
    use SessionMixin;
    use StaffRepositoryMixin;
    use StaffRememberTokenRepositoryMixin;
    use UnitSupport;

    public const COOKIE_NAME = 'REMEMBER_TOKEN';

    private Staff $staff;
    private StaffRememberToken $staffRememberToken;

    /**
     * @var \App\Resolvers\StaffResolverImpl|\Laravel\Lumen\Application
     */
    private $resolver;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (StaffResolverImplTest $self): void {
            $self->staff = $self->examples->staffs[0]->copy([
                'isEnabled' => true,
                'isVerified' => true,
            ]);
            $self->staffRememberToken = $self->examples->staffRememberTokens[0]->copy([
                'expiredAt' => Carbon::tomorrow(),
            ]);

            $self->config->allows('get')->with('zinger.remember_token.cookie_name')->andReturn(self::COOKIE_NAME);

            $self->officeRepository
                ->allows('lookup')
                ->with(...$self->staff->officeIds)
                ->andReturn(Seq::from($self->examples->offices[0]))
                ->byDefault();

            $self->roleRepository
                ->allows('lookup')
                ->with(...$self->staff->roleIds)
                ->andReturn(Seq::from($self->examples->roles[0]))
                ->byDefault();

            $self->staffRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->staff))
                ->byDefault();

            $self->staffRememberTokenRepository
                ->allows('lookup')
                ->andReturn(Seq::from($self->staffRememberToken))
                ->byDefault();

            $self->session
                ->allows('get')
                ->with('staffId')
                ->andReturnNull()
                ->byDefault();

            $self->resolver = app(StaffResolverImpl::class);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_resolve(): void
    {
        $this->should('return a some of Staff', function (): void {
            $this->session
                ->allows('get')
                ->with('staffId')
                ->andReturn($this->staff->id);

            $request = $this->createRequest();
            $actual = $this->resolver->resolve($request);

            $this->assertInstanceOf(Some::class, $actual);
            $this->assertModelStrictEquals($this->staff, $actual->get());
        });
        $this->should('lookup a Staff when session exists', function (): void {
            $this->session
                ->allows('get')
                ->with('staffId')
                ->andReturn($this->staff->id);
            $this->staffRepository
                ->expects('lookup')
                ->with($this->staff->id)
                ->andReturn(Seq::from($this->staff));

            $request = $this->createRequest();
            $this->resolver->resolve($request);
        });
        $this->should('lookup a StaffRememberToken when session not exists', function (): void {
            $this->staffRememberTokenRepository
                ->expects('lookup')
                ->with($this->staffRememberToken->id)
                ->andReturn(Seq::from($this->staffRememberToken));

            $cookies = [self::COOKIE_NAME => Json::encode($this->rememberCookie())];
            $request = $this->createRequest($cookies);
            $this->resolver->resolve($request);
        });
        $this->should('lookup a StaffRememberToken when Staff is not enabled', function (): void {
            $this->session
                ->allows('get')
                ->with('staffId')
                ->andReturn($this->staff->id);
            $this->staffRepository
                ->allows('lookup')
                ->andReturn(
                    Seq::from($this->staff->copy(['isEnabled' => false])),
                    Seq::from($this->staff)
                );
            $this->staffRememberTokenRepository
                ->expects('lookup')
                ->with($this->staffRememberToken->id)
                ->andReturn(Seq::from($this->staffRememberToken));

            $cookies = [self::COOKIE_NAME => Json::encode($this->rememberCookie())];
            $request = $this->createRequest($cookies);
            $this->resolver->resolve($request);
        });
        $this->should('lookup a StaffRememberToken when Staff is not verified', function (): void {
            $this->session
                ->allows('get')
                ->with('staffId')
                ->andReturn($this->staff->id);
            $this->staffRepository
                ->allows('lookup')
                ->andReturn(
                    Seq::from($this->staff->copy(['isVerified' => false])),
                    Seq::from($this->staff)
                );
            $this->staffRememberTokenRepository
                ->expects('lookup')
                ->with($this->staffRememberToken->id)
                ->andReturn(Seq::from($this->staffRememberToken));

            $cookies = [self::COOKIE_NAME => Json::encode($this->rememberCookie())];
            $request = $this->createRequest($cookies);
            $this->resolver->resolve($request);
        });
        $this->should('lookup a StaffRememberToken when failed to lookup a Staff', function (): void {
            $this->session
                ->allows('get')
                ->with('staffId')
                ->andReturn($this->staff->id);
            $this->staffRepository
                ->allows('lookup')
                ->andReturn(Seq::emptySeq());
            $this->staffRememberTokenRepository
                ->expects('lookup')
                ->with($this->staffRememberToken->id)
                ->andReturn(Seq::from($this->staffRememberToken));

            $cookies = [self::COOKIE_NAME => Json::encode($this->rememberCookie())];
            $request = $this->createRequest($cookies);

            $this->resolver->resolve($request);
        });
        $this->should('lookup a Staff when StaffRememberToken is found', function (): void {
            $this->staffRepository
                ->expects('lookup')
                ->with($this->staffRememberToken->staffId)
                ->andReturn(Seq::from($this->staff));

            $cookies = [self::COOKIE_NAME => Json::encode($this->rememberCookie())];
            $request = $this->createRequest($cookies);
            $this->resolver->resolve($request);
        });
        $this->should('return None when remember cookie not exists', function (): void {
            $this->staffRepository
                ->allows('lookup')
                ->andReturn(Seq::from($this->staff));

            $request = $this->createRequest();
            $this->assertInstanceOf(None::class, $this->resolver->resolve($request));
        });
        $this->should('return None when remember cookie is not json', function (): void {
            $this->staffRepository
                ->allows('lookup')
                ->andReturn(Seq::from($this->staff));

            $cookies = [self::COOKIE_NAME => 'aaaaa'];
            $request = $this->createRequest($cookies);
            $this->assertInstanceOf(None::class, $this->resolver->resolve($request));
        });
        $this->should('return None when StaffRememberToken has different Staff id', function (): void {
            $this->staffRepository
                ->allows('lookup')
                ->andReturn(Seq::from($this->staff));

            $data = ['staffId' => $this->examples->staffs[1]->id] + $this->rememberCookie();
            $cookies = [self::COOKIE_NAME => Json::encode($data)];
            $request = $this->createRequest($cookies);
            $this->resolver->resolve($request);
            $this->assertInstanceOf(None::class, $this->resolver->resolve($request));
        });
        $this->should('return None when StaffRememberToken has different token', function (): void {
            $this->staffRepository->allows('lookup')->andReturn(Seq::from($this->staff));

            $data = ['token' => 'DIFFERENT_TOKEN'] + $this->rememberCookie();
            $cookies = [self::COOKIE_NAME => Json::encode($data)];
            $request = $this->createRequest($cookies);
            $this->assertInstanceOf(None::class, $this->resolver->resolve($request));
        });
        $this->should('return None when StaffRememberToken is expired', function (): void {
            $this->staffRepository
                ->allows('lookup')
                ->andReturn(Seq::from($this->staff));
            $this->staffRememberTokenRepository
                ->allows('lookup')
                ->andReturn(Seq::from($this->staffRememberToken->copy(['expiredAt' => Carbon::yesterday()])));

            $cookies = [self::COOKIE_NAME => Json::encode($this->rememberCookie())];
            $request = $this->createRequest($cookies);
            $this->assertInstanceOf(None::class, $this->resolver->resolve($request));
        });
        $this->should('return None when StaffRememberToken is not related to Staff', function (): void {
            $this->staffRepository->allows('lookup')->andReturn(Seq::emptySeq());

            $cookies = [self::COOKIE_NAME => Json::encode($this->rememberCookie())];
            $request = $this->createRequest($cookies);
            $this->assertInstanceOf(None::class, $this->resolver->resolve($request));
        });
        $this->should(
            'return None when StaffRememberToken is related to Staff that is not enabled',
            function (): void {
                $this->staffRepository
                    ->allows('lookup')
                    ->andReturn(Seq::from($this->staff->copy(['isEnabled' => false])));

                $cookies = [self::COOKIE_NAME => Json::encode($this->rememberCookie())];
                $request = $this->createRequest($cookies);
                $this->assertInstanceOf(None::class, $this->resolver->resolve($request));
            }
        );
        $this->should(
            'return None when StaffRememberToken is related to Staff that is not verified',
            function (): void {
                $this->staffRepository
                    ->allows('lookup')
                    ->andReturn(Seq::from($this->staff->copy(['isEnabled' => false])));

                $cookies = [self::COOKIE_NAME => Json::encode($this->rememberCookie())];
                $request = $this->createRequest($cookies);
                $this->assertInstanceOf(None::class, $this->resolver->resolve($request));
            }
        );
        $this->should('resolve StaffRequest with OfficeGroup', function (): void {
            $staff = $this->examples->staffs[3]->copy([
                'officeGroupIds' => [$this->examples->officeGroups[2]->id],
                'isEnabled' => true,
                'isVerified' => true,
            ]);
            $this->session
                ->allows('get')
                ->with('staffId')
                ->andReturn($staff->id);
            $this->staffRepository
                ->expects('lookup')
                ->andReturn(Seq::from($staff));

            $roles = Seq::from($this->examples->roles[0]);
            $this->roleRepository
                ->expects('lookup')
                ->with(...$staff->roleIds)
                ->andReturn($roles);
            $offices = Seq::from($this->examples->offices[0]);
            $this->officeRepository
                ->expects('lookup')
                ->with(...$staff->officeIds)
                ->andReturn($offices);
            $this->officeGroupRepository
                ->expects('lookup')
                ->with(...$staff->officeGroupIds)
                ->andReturn(Seq::from($this->examples->officeGroups[2]));
            $this->officeGroupFinder
                ->expects('find')
                ->with(
                    equalTo(['parentOfficeGroupIds' => [$this->examples->officeGroups[2]->id]]),
                    equalTo(['all' => true, 'sortBy' => 'id'])
                )
                ->andReturn(FinderResult::from(Seq::from($this->examples->officeGroups[3]), Pagination::create()));
            $this->officeGroupFinder
                ->expects('find')
                ->with(
                    equalTo(['parentOfficeGroupIds' => [$this->examples->officeGroups[3]->id]]),
                    equalTo(['all' => true, 'sortBy' => 'id'])
                )
                ->andReturn(FinderResult::from(Seq::emptySeq(), Pagination::create()));
            $groupOffices = Seq::fromArray([
                $this->examples->offices[0],
                $this->examples->offices[1],
            ]);
            $this->officeFinder
                ->expects('find')
                ->with(
                    equalTo([
                        'officeGroupIds' => [
                            $this->examples->officeGroups[3]->id,
                            $this->examples->officeGroups[2]->id,
                        ],
                    ]),
                    equalTo(['all' => true, 'sortBy' => 'id'])
                )
                ->andReturn(FinderResult::from($groupOffices, Pagination::create()));

            $request = $this->createRequest();
            $expectedRequest = $this->createRequest();
            StaffRequest::prepareStaffRequest($expectedRequest, $staff, $roles, $offices, $groupOffices);

            $this->resolver->resolve($request);

            $this->assertEquals($expectedRequest->toAssoc(), $request->toAssoc());
        });
    }

    /**
     * Create StaffResolverImpl instance.
     *
     * @param array $cookies
     * @return \App\Http\Requests\StaffRequest
     */
    private function createRequest($cookies = []): StaffRequest
    {
        $request = StaffRequest::create('/', 'GET', [], $cookies);
        $request->setLaravelSession($this->session);
        return $request;
    }

    /**
     * リメンバートークンCookieに格納されている内容.
     *
     * @return array
     */
    private function rememberCookie(): array
    {
        return [
            'id' => $this->staffRememberToken->id,
            'staffId' => $this->staffRememberToken->staffId,
            'token' => $this->staffRememberToken->token,
        ];
    }
}
