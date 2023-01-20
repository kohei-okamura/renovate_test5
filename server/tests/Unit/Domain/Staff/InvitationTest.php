<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Domain\Staff;

use Domain\Common\Carbon;
use Domain\Staff\Invitation;
use Illuminate\Support\Arr;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\Unit\Examples\ExamplesConsumer;
use Tests\Unit\Helpers\UnitSupport;
use Tests\Unit\Mixins\CarbonMixin;
use Tests\Unit\Test;

/**
 * {@link \Domain\Staff\Invitation} Test.
 */
class InvitationTest extends Test
{
    use CarbonMixin;
    use ExamplesConsumer;
    use MatchesSnapshots;
    use UnitSupport;
    protected array $values = [];

    private Invitation $invitation;

    /**
     * セットアップ処理.
     */
    public static function _setUpSuite(): void
    {
        static::beforeEachSpec(function (InvitationTest $self): void {
            $self->values = [
                'id' => 1,
                'staffId' => $self->examples->staffs[0]->id,
                'email' => 'sample@example.com',
                'token' => 'eustylelab',
                'roleIds' => [$self->examples->roles[0]->id],
                'officeIds' => [$self->examples->offices[0]->id],
                'expiredAt' => Carbon::now(),
                'createdAt' => Carbon::now(),
            ];
            $self->invitation = Invitation::create($self->values);
        });
    }

    /**
     * @test
     * @return void
     */
    public function describe_attrs(): void
    {
        $this->should(
            'have specified attribute',
            function (string $key): void {
                $this->assertSame($this->invitation->get($key), Arr::get($this->values, $key));
            },
            [
                'examples' => [
                    'staffId' => ['staffId'],
                    'email' => ['email'],
                    'token' => ['token'],
                    'roleIds' => ['roleIds'],
                    'officeIds' => ['officeIds'],
                    'expiredAt' => ['expiredAt'],
                    'createdAt' => ['createdAt'],
                ],
            ]
        );
    }

    /**
     * @test
     * @return void
     */
    public function describe_json(): void
    {
        $this->should('changes to JSON encode results', function (): void {
            $this->assertMatchesJsonSnapshot($this->invitation);
        });
    }
}
