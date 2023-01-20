/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
type Mocked = {
  <T extends (...args: any[]) => any> (target: T): T extends () => infer R
    ? jest.Mock<R, []>
    : T extends (...args: infer A) => infer R
      ? jest.Mock<R, A>
      : never
}

export const mocked: Mocked = (target: any) => target
