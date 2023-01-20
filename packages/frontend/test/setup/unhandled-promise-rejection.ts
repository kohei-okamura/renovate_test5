/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
process.on('unhandledRejection', (reason, promise) => {
  console.log('Unhandled promise rejection', { promise, reason })
  fail('Unhandled promise rejection')
})
