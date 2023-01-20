/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import * as AWS from 'aws-sdk'

// See https://docs.aws.amazon.com/AWSJavaScriptSDK/latest/AWS/S3.html
const apiVersion = '2006-03-01'

export const createS3Service = () => new AWS.S3({ apiVersion })
