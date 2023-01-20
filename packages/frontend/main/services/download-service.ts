/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import Vue from 'vue'

export type DownloadService = Readonly<{
  blob (data: BlobPart, filename: string): Promise<void>
  uri (uri: string, filename?: string): Promise<void>
}>

async function uri (uri: string, filename?: string): Promise<void> {
  const element = document.createElement('a')
  element.setAttribute('href', uri)
  if (filename) {
    element.setAttribute('download', filename)
  }
  document.body.appendChild(element)
  element.click()
  await Vue.nextTick()
  document.body.removeChild(element)
}

async function blob (data: BlobPart, filename: string): Promise<void> {
  await uri(window.URL.createObjectURL(new Blob([data])), filename)
}

export const createDownloadService = (): DownloadService => ({ blob, uri })
