<?php
/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare(strict_types=1);

namespace App\Http\Requests\Delegates;

use Domain\Context\Context;
use Domain\ProvisionReport\LtcsProvisionReportEntry;
use Illuminate\Validation\Validator;

/**
 * 介護保険サービス：予実フォーム.
 */
interface LtcsProvisionReportFormDelegate
{
    /**
     * 介護保険サービス：予実：サービス情報 相当の情報を含む配列 を 介護保険サービス：予実：サービス情報 モデルの配列に変換する.
     *
     * @param array $entries
     * @return LtcsProvisionReportEntry[]
     */
    public function convertEntryArrayToModel(array $entries): array;

    /**
     * バリデータインスタンスの設定.
     *
     * @param \Domain\Context\Context $context
     * @param \Illuminate\Validation\Validator $validator
     */
    public function setValidator(Context $context, Validator $validator): void;

    /**
     * 共通的なバリデーションルールを作成する.
     *
     * @param array $input
     * @return \string[][]
     */
    public function createRules(array $input): array;

    /**
     * 共通的な Validation attributes を返す.
     *
     * @return string[]
     */
    public function getAttributes(): array;

    /**
     * 共通的な エラーメッセージ を返す.
     *
     * @return string[]
     */
    public function getErrorMessages(): array;
}
