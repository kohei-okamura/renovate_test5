<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\HttpContext;
use Domain\Config\Config;
use Domain\Context\Context;
use Domain\Organization\Organization;
use Lib\Exceptions\LogicException;
use ScalikePHP\Option;
use ScalikePHP\Seq;

/**
 * 事業者情報を持つリクエスト.
 */
class OrganizationRequest extends BaseRequest
{
    private ?Organization $organization = null;

    /**
     * 事前準備処理.
     *
     * @param \App\Http\Requests\OrganizationRequest $request
     * @param \Domain\Organization\Organization $organization
     * @return void
     */
    public static function prepareOrganizationRequest(OrganizationRequest $request, Organization $organization): void
    {
        $request->organization = $organization;
    }

    /**
     * コンテキストを生成する.
     *
     * @return \Domain\Context\Context
     */
    protected function createContext(): Context
    {
        return new HttpContext(
            $this->organization(),
            Option::none(),
            Seq::emptySeq(),
            $this->baseUri(),
            Seq::emptySeq(),
            Seq::emptySeq()
        );
    }

    /**
     * ベース URI を返却する.
     *
     * @return string
     */
    final protected function baseUri(): string
    {
        $config = app(Config::class);
        return sprintf(
            '%s://%s.%s/%s/',
            $config->get('zinger.uri.scheme'),
            $this->organization->code,
            $config->get('zinger.uri.app_domain'),
            $config->get('zinger.uri.base_path'),
        );
    }

    /**
     * 事業者情報.
     *
     * @return \Domain\Organization\Organization
     */
    protected function organization(): Organization
    {
        if ($this->organization === null) {
            throw new LogicException('OrganizationRequest does not have any organization instance.');
        }
        return $this->organization;
    }
}
