/*
 * Copyright © 2022 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */

export const parseError = (error: unknown) => error instanceof Error
  ? {
    name: error.name,
    message: error.message,
    stack: error.stack
  }
  : error
