<?php
/**
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Mails;

use Illuminate\Mail\Mailable;

/**
 * Abstract Mail Builder.
 */
abstract class AbstractMailBuilder implements MailBuilder
{
    /**
     * To.
     */
    protected string $to = '';

    /**
     * 事業者コード.
     */
    protected string $organizationCode = '';

    /**
     * To を設定する.
     *
     * @param string $to
     * @return \App\Mails\AbstractMailBuilder
     */
    public function to(string $to): self
    {
        $this->to = $to;
        return $this;
    }

    /**
     * 事業者コードを設定する.
     *
     * @param string $organizationCode
     * @return \App\Mails\AbstractMailBuilder
     */
    public function organizationCode(string $organizationCode): self
    {
        $this->organizationCode = $organizationCode;
        return $this;
    }

    /** {@inheritdoc} */
    public function build(): Mailable
    {
        return Mail::make()
            ->text($this->view(), $this->params() + $this->defaultParams())
            ->subject($this->subject())
            ->to($this->to);
    }

    /**
     * View に渡すデフォルトパラメータ.
     *
     * @return array
     */
    protected function defaultParams(): array
    {
        return [
            'loginUrl' => "https://{$this->organizationCode}.careid.jp/",
        ];
    }

    /**
     * 件名.
     *
     * @return string
     */
    abstract protected function subject(): string;

    /**
     * メールテンプレート名.
     *
     * @return string
     */
    abstract protected function view(): string;

    /**
     * View に渡すパラメータ.
     *
     * @return array
     */
    abstract protected function params(): array;
}
