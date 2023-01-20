/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

// TODO: rfdc 等のディープコピーを実現するライブラリを導入する
export const clone = <T> (x: T): T => x === undefined ? undefined : JSON.parse(JSON.stringify(x))
