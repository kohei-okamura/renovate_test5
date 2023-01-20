<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers;

use App\Providers\Dependencies\AppDependencies;
use App\Providers\Dependencies\BankAccountDependencies;
use App\Providers\Dependencies\BillingDependencies;
use App\Providers\Dependencies\CallingDependencies;
use App\Providers\Dependencies\ContractDependencies;
use App\Providers\Dependencies\DependenciesInterface;
use App\Providers\Dependencies\DwsAreaGradeDependencies;
use App\Providers\Dependencies\DwsCertificationDependencies;
use App\Providers\Dependencies\FileDependencies;
use App\Providers\Dependencies\JobDependencies;
use App\Providers\Dependencies\LocationDependencies;
use App\Providers\Dependencies\LtcsAreaGradeDependencies;
use App\Providers\Dependencies\LtcsInsCardDependencies;
use App\Providers\Dependencies\OfficeDependencies;
use App\Providers\Dependencies\OrganizationDependencies;
use App\Providers\Dependencies\OwnExpenseProgramDependencies;
use App\Providers\Dependencies\PermissionDependencies;
use App\Providers\Dependencies\ProjectDependencies;
use App\Providers\Dependencies\ProvisionReportDependencies;
use App\Providers\Dependencies\RoleDependencies;
use App\Providers\Dependencies\ServiceCodeDictionaryDependencies;
use App\Providers\Dependencies\ShiftDependencies;
use App\Providers\Dependencies\StaffDependencies;
use App\Providers\Dependencies\UserBillingDependencies;
use App\Providers\Dependencies\UserDependencies;
use Domain\Common\Carbon;
use Illuminate\Support\DateFactory;
use Illuminate\Support\ServiceProvider;

/**
 * Application Service Provider.
 *
 * @codeCoverageIgnore APPに処理が来る前のコードなのでUnitTest除外
 */
final class AppServiceProvider extends ServiceProvider
{
    /**
     * Dependencies Classes.
     */
    public const DEPENDENCIES_CLASSES = [
        AppDependencies::class,
        BankAccountDependencies::class,
        BillingDependencies::class,
        CallingDependencies::class,
        ContractDependencies::class,
        DwsAreaGradeDependencies::class,
        DwsCertificationDependencies::class,
        FileDependencies::class,
        JobDependencies::class,
        LocationDependencies::class,
        LtcsAreaGradeDependencies::class,
        LtcsInsCardDependencies::class,
        OfficeDependencies::class,
        OrganizationDependencies::class,
        OwnExpenseProgramDependencies::class,
        PermissionDependencies::class,
        ProjectDependencies::class,
        ProvisionReportDependencies::class,
        RoleDependencies::class,
        ServiceCodeDictionaryDependencies::class,
        ShiftDependencies::class,
        StaffDependencies::class,
        UserDependencies::class,
        UserBillingDependencies::class,
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        foreach (self::DEPENDENCIES_CLASSES as $dependenciesClass) {
            $this->registerDependencies($dependenciesClass);
        }
        DateFactory::useClass(Carbon::class);
    }

    /**
     * 起動時の処理.
     *
     * @return void
     */
    public function boot()
    {
        setlocale(\LC_ALL, env('LC_ALL', 'ja_JP.UTF-8'));
        Carbon::setLocale('ja');
    }

    /**
     * Register dependencies.
     *
     * @param string $dependenciesClass
     */
    private function registerDependencies(string $dependenciesClass): void
    {
        $dependencies = app($dependenciesClass);
        assert($dependencies instanceof DependenciesInterface);
        $list = $dependencies->getDependenciesList();
        foreach ($list as $abstract => $concrete) {
            $this->app->singleton($abstract, $concrete);
        }
    }
}
