/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import * as AWS from 'aws-sdk'
import { Readable } from 'stream'
import { createCloudWatchLogsService } from '~aws/scripts/utils/create-cloud-watch-logs-service'

type Options = {
  logGroup: string
  logStream: string
  endOfStreamIdentifier: string
  durationBetweenPolls?: number
  timeoutBeforeFirstLogs?: number
}

const now = () => (new Date()).getTime()

export class AwsLogStream extends Readable {
  private readonly options: Required<Options>
  private readonly logs: AWS.CloudWatchLogs

  private buffer: AWS.CloudWatchLogs.OutputLogEvents = []
  private exitCode = 0
  private logsReceived = false
  private nextToken: string | undefined
  private pending = false
  private startedAt: number | undefined
  private streamEnded = false
  private stopRequested = false

  constructor (options: Options) {
    super({ objectMode: true })
    this.options = {
      durationBetweenPolls: 1000,
      timeoutBeforeFirstLogs: 300 * 1000,
      ...options
    }
    this.logs = createCloudWatchLogsService()
  }

  _read () {
    let active = true
    while (active && this.buffer.length > 0) {
      const x = this.buffer.shift()!
      this.push(x.message + '\n', 'utf-8')
      active = !!x
    }
    if (!active) {
      setTimeout(() => this._read(), 100)
    }
    if (this.streamEnded || this.stopRequested) {
      this.push(null)
    } else if (active && !this.pending) {
      this.fetchLogs()
    }
  }

  shutDown () {
    this.stopRequested = true
  }

  private fetchLogs (): void {
    this.pending = true
    this.startedAt = this.startedAt ?? now()
    const startedAt = this.startedAt!
    const request: AWS.CloudWatchLogs.GetLogEventsRequest = {
      logGroupName: this.options.logGroup,
      logStreamName: this.options.logStream,
      startFromHead: true,
      nextToken: this.nextToken
    }
    const next = () => {
      setTimeout(() => this._read(), this.options.durationBetweenPolls)
    }
    const emitError = (reason: string | Error) => {
      const error = typeof reason === 'string' ? new Error(reason) : reason
      process.nextTick(() => this.emit('error', error))
    }
    this.logs.getLogEvents(request, (error, data) => {
      this.pending = false
      if (error) {
        error.code === 'ResourceNotFoundException' ? next() : emitError(error)
      } else {
        const events = data.events ?? []
        if (events.length > 0) {
          this.nextToken = data.nextForwardToken
          this.logsReceived = true
          this.buffer.push(...events)
        }
        if (!this.logsReceived && (now() - startedAt) > this.options.timeoutBeforeFirstLogs) {
          emitError(`No logs received before timeoutBeforeFirstLogs option set at '${this.options.timeoutBeforeFirstLogs}'`)
        } else {
          const endOfStreamIdentifierBase64 = Buffer.from(this.options.endOfStreamIdentifier).toString('base64')
          const end = events.find(x => x.message?.includes(endOfStreamIdentifierBase64) ?? false)
          if (this.stopRequested) {
            this.exitCode = 130
          }
          if (end) {
            this.streamEnded = true
            const m = end.message?.match(/EXITCODE: (\d+)/)
            this.exitCode = m ? (+m[1] ?? 0) : 0
          }
          next()
        }
      }
    })
  }
}
