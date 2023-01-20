/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import * as AWS from 'aws-sdk'

// See https://docs.aws.amazon.com/AWSJavaScriptSDK/latest/AWS/SSM.html
const apiVersion = '2014-11-06'

export const createSsmService = () => new AWS.SSM({ apiVersion })
