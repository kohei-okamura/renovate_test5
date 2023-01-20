/*
 * Copyright © 2019 EUSTYLE LABORATORY - ALL RIGHTS RESERVED.
 * UNAUTHORIZED COPYING OF THIS FILE, VIA ANY MEDIUM IS STRICTLY PROHIBITED PROPRIETARY AND CONFIDENTIAL.
 */
module.exports = require('babel-jest').createTransformer({
  babelrc: false,
  presets: [
    ['@babel/preset-env', { targets: { node: 'current' } }]
  ]
})
