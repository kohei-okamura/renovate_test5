/*
 * Copyright © 2021 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { User } from '~/models/user'

export const resolveUserStatus = (isEnabled: User['isEnabled']) => isEnabled ? '利用中' : '利用終了'
