/*
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
define([
    'underscore',
    'jquery',
    'pim/product-edit-form/associated-product-row/original',
], function (
    _,
    $,
    BaseRow
) {
    return BaseRow.extend({
        canRemoveAssociation: function () {
            return false;
        }
    });
});
