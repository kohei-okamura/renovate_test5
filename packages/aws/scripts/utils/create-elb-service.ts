/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import * as AWS from 'aws-sdk'

// See https://docs.aws.amazon.com/AWSJavaScriptSDK/latest/AWS/ELBv2.html
const apiVersion = '2015-12-01'

export const createElbService = () => new AWS.ELBv2({ apiVersion })
