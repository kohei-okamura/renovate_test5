/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import * as AWS from 'aws-sdk'

// See https://docs.aws.amazon.com/AWSJavaScriptSDK/latest/AWS/CloudWatchLogs.html
const apiVersion = '2014-03-28'

export const createCloudWatchLogsService = () => new AWS.CloudWatchLogs({ apiVersion })
