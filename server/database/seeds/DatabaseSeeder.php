<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * 初期データ Seeder.
 */
final class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call([
            OrganizationSeeder::class,
            DwsAreaGradeSeeder::class,
            DwsAreaGradeFeeSeeder::class,
            LtcsAreaGradeSeeder::class,
            LtcsAreaGradeFeeSeeder::class,
            PermissionGroupSeeder::class,
            RoleSeeder::class,
            DwsProjectServiceMenuSeeder::class,
            LtcsProjectServiceMenuSeeder::class,
        ]);
    }
}
