/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import { Permission } from '@zinger/enums/lib/permission'
import { RefOrValue } from '~/support/reactive'

export namespace Menu {

  /**
   * メニュー項目.
   */
  type BaseItem = Readonly<{
    disabled?: RefOrValue<boolean>
    icon?: string
    permissions?: Permission[]
    text: RefOrValue<string>
  }>
  type ActionItem = BaseItem & Readonly<{
    action: () => void | Promise<void>
    to?: string
  }>
  type LinkItem = BaseItem & Readonly<{
    to: string
  }>
  export type Item = ActionItem | LinkItem

  /**
   * メニューグループ.
   */
  export type Group = Readonly<{
    avatar?: string
    children: Item[]
    group?: string
    icon?: string
    permissions?: Permission[]
    text: string
  }>

  /**
   * メニュー要素.
   */
  export type Element = Item | Group

}
