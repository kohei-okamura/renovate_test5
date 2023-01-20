/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
declare namespace UserPropertiesManager {
  type Manager = {
    get (key: string): string | null
    getSafety (key: string, title: string): string
    show (ui: GoogleAppsScript.Base.Ui, key: string, title: string): void
    prompt (ui: GoogleAppsScript.Base.Ui, key: string, title: string): void
  }

  // noinspection JSUnusedGlobalSymbols
  const createManager: (store: GoogleAppsScript.Properties.Properties) => Manager
}
