/*
 * Copyright © 2020 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
import * as AWS from 'aws-sdk'

// See // see https://docs.aws.amazon.com/AWSJavaScriptSDK/latest/AWS/EC2.html
const apiVersion = '2016-11-15'

export const createEc2Service = () => new AWS.EC2({ apiVersion })
