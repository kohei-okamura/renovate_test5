/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

/**
 * 指定された値が「空」かどうかを判定する.
 *
 * 以下の場合に「空」と判定する.
 *
 * - 値が null である場合.
 * - 値が undefined である場合.
 * - 値が空文字である場合.
 */
export const isEmpty = (x: unknown): x is null | undefined | '' => x === null || x === undefined || x === ''
