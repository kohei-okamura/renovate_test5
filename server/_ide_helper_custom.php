<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace Illuminate\Database\Schema {
    /**
     * Class Blueprint
     *
     * @method void addr(string $prefix = '', string $commentPrefix = '', null|string $after = null)
     * @method void attr($base)
     * @method ColumnDefinition birthday(string $prefix = '', string $commentPrefix = '')
     * @method null|string buildForeignKeyName(string $columnName)
     * @method ForeignDefinitionProxy|ForeignKeyDefinition|\Illuminate\Support\Fluent catalogued(string $to, string $comment, null|string $name = null, null|string $after = null, $default = null)
     * @method ColumnDefinition code(int $length)
     * @method ColumnDefinition createdAt()
     * @method ColumnDefinition email(string $comment = "メールアドレス")
     * @method ColumnDefinition expiredAt()
     * @method ColumnDefinition fax(string $comment = "FAX番号")
     * @method ColumnDefinition fixedAt()
     * @method ForeignDefinitionProxy|ForeignKeyDefinition|\Illuminate\Support\Fluent foreign(string|string[] $columns, null|string $name = null)
     * @method bool hasForeignKey(string $identifier)
     * @method ColumnDefinition id()
     * @method array listTableForeignKeys()
     * @method void location()
     * @method ColumnDefinition password(string $comment = "パスワード")
     * @method ForeignDefinitionProxy|ForeignKeyDefinition|\Illuminate\Support\Fluent references(string $to, string $comment, null|string $name = null)
     * @method ColumnDefinition serviceCode()
     * @method ColumnDefinition sex(string $prefix = '', string $commentPrefix = '')
     * @method ColumnDefinition sortOrder()
     * @method ForeignDefinitionProxy|ForeignKeyDefinition|\Illuminate\Support\Fluent stringCatalogued(string $to, string $comment, null|string $name = null)
     * @method void structuredName(string $prefix = '', string $commentPrefix = '')
     * @method ColumnDefinition tel(string $prefix = '', string $commentPrefix = '')
     * @method ColumnDefinition updatedAt()
     *
     * @see \App\Support\BlueprintMixin
     */
    class Blueprint
    {
    }

    interface ForeignDefinitionProxy
    {
        /**
         * @param string $name
         * @return ForeignDefinitionProxy|ForeignKeyDefinition|\Illuminate\Support\Fluent
         */
        public function index(string $name);
    }
}

namespace Faker {
    /**
     * Class Generator
     *
     * @method \Domain\Common\Addr addr()
     * @method string domain()
     * @method string emailAddress()
     * @method \Domain\Common\StructuredName name(\Domain\Common\Sex $sex)
     * @method array officeName()
     * @property-read \Domain\Common\Addr $addr
     * @property-read string $domain
     * @property-read string $emailAddress
     * @property-read array $officeName
     *
     * @see \App\Providers\FakerServiceProvider
     */
    class Generator
    {
    }
}

namespace Illuminate\Log {
    /**
     * Class Logger
     *
     * @method \Monolog\Handler\FormattableHandlerInterface[] getHandlers()
     */
    class Logger
    {
    }
}
