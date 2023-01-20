/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { toNarrowAlphanumeric } from 'jaco'

/**
 * 文字列を正規化する.
 */
export const normalizeString = (input: string): string => toNarrowAlphanumeric(input).replace(/．/g, '.')
