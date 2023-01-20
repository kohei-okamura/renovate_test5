<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Tests\Unit\Examples;

use Domain\Common\Carbon;
use Domain\Staff\Invitation;
use Faker\Generator;

/**
 * Invitation Example.
 *
 * @property-read \Domain\Staff\Invitation[] $invitations
 * @mixin \Tests\Unit\Examples\StaffExample
 * @mixin \Tests\Unit\Examples\RoleExample
 * @mixin \Tests\Unit\Examples\OfficeExample
 */
trait InvitationExample
{
    /**
     * 招待の一覧を生成する.
     *
     * @return \Domain\Staff\Invitation[]
     */
    protected function invitations(): array
    {
        $faker = app(Generator::class);
        return [
            $this->generateInvitation([
                'id' => 1,
            ], $faker),
            $this->generateInvitation([
                'id' => 2,
                'staffId' => $this->staffs[1]->id,
            ], $faker),
            $this->generateInvitation([
                'id' => 3,
                'officeIds' => [
                    $this->offices[1]->id,
                    $this->offices[2]->id,
                ],
            ], $faker),
            $this->generateInvitation([
                'id' => 4,
                'roleIds' => [
                    $this->roles[1]->id,
                    $this->roles[8]->id,
                ],
            ], $faker),
            $this->generateInvitation([
                'id' => 5,
                'expiredAt' => Carbon::now()->microsecond(0)->subMinute(),
            ], $faker),
            $this->generateInvitation([
                'id' => 6,
                'email' => 'eustylelab@example.com',
            ], $faker),
            $this->generateInvitation([
                'id' => 7,
                'email' => $this->staffs[34]->email,
            ], $faker),
        ];
    }

    /**
     * Generate an example of Invitation.
     *
     * @param array $overwrites
     * @param \Faker\Generator $faker
     * @return \Domain\Staff\Invitation
     */
    protected function generateInvitation(array $overwrites, Generator $faker)
    {
        $attrs = [
            'staffId' => $this->staffs[0]->id,
            'email' => $faker->emailAddress,
            'token' => $faker->text(60),
            'roleIds' => [$this->roles[0]->id],
            'officeIds' => [$this->offices[0]->id],
            'officeGroupIds' => [$this->officeGroups[1]->id],
            'expiredAt' => Carbon::parse('2030-01-01')->microsecond(0),
            'createdAt' => Carbon::instance($faker->dateTime),
        ];
        return Invitation::create($overwrites + $attrs);
    }
}
