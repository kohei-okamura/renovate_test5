<?php
/**
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Providers\Dependencies;

use App\Concretes\ConfigRepository;
use App\Concretes\PdfCreatorImpl;
use App\Concretes\TokenMakerImpl;
use App\Concretes\TransactionManagerFactoryImpl;
use App\Concretes\UrlBuilderImpl;
use App\Console\OrganizationIterator;
use App\Console\OrganizationIteratorImpl;
use App\Events\EventDispatcher;
use App\Resolvers\OrganizationResolver;
use App\Resolvers\OrganizationResolverImpl;
use App\Resolvers\StaffResolver;
use App\Resolvers\StaffResolverImpl;
use App\Validations\ConfirmShiftAsyncValidatorImpl;
use App\Validations\CreateWithdrawalTransactionAsyncValidatorImpl;
use App\Validations\ImportShiftAsyncValidatorImpl;
use Domain\Config\Config;
use Domain\Event\EventDispatcher as DomainEventDispatcher;
use Domain\Pdf\PdfCreator;
use Domain\ShoutUrl\UrlShortenerGateway;
use Domain\TransactionManagerFactory;
use Domain\Url\UrlBuilder;
use Domain\Validator\ConfirmShiftAsyncValidator;
use Domain\Validator\CreateWithdrawalTransactionAsyncValidator;
use Domain\Validator\ImportShiftAsyncValidator;
use Illuminate\Bus\Dispatcher;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Infrastructure\ShortUrl\UrlShortenerGatewayImpl;
use UseCase\Contracts\TokenMaker;

/**
 * App Dependencies.
 *
 * @codeCoverageIgnore APPに処理が来る前のコードなのでUnitTest除外
 */
final class AppDependencies implements DependenciesInterface
{
    /** {@inheritdoc} */
    public function getDependenciesList(): iterable
    {
        return [
            Config::class => ConfigRepository::class,
            ConfirmShiftAsyncValidator::class => ConfirmShiftAsyncValidatorImpl::class,
            CreateWithdrawalTransactionAsyncValidator::class => CreateWithdrawalTransactionAsyncValidatorImpl::class,
            DomainEventDispatcher::class => EventDispatcher::class,
            ImportShiftAsyncValidator::class => ImportShiftAsyncValidatorImpl::class,
            OrganizationIterator::class => OrganizationIteratorImpl::class,
            OrganizationResolver::class => OrganizationResolverImpl::class,
            PdfCreator::class => PdfCreatorImpl::class,
            QueueingDispatcher::class => Dispatcher::class,
            StaffResolver::class => StaffResolverImpl::class,
            TokenMaker::class => TokenMakerImpl::class,
            TransactionManagerFactory::class => TransactionManagerFactoryImpl::class,
            UrlBuilder::class => UrlBuilderImpl::class,
            UrlShortenerGateway::class => UrlShortenerGatewayImpl::class,
        ];
    }
}
