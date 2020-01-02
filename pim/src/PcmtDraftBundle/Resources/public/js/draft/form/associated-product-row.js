/*
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
define([
    'underscore',
    'jquery',
    'pim/product-edit-form/associated-product-row/original',
    'oro/mediator',
], function (
    _,
    $,
    BaseRow,
    mediator
) {
    return BaseRow.extend({

        render() {
            BaseRow.prototype.render.call(this, arguments);

            const row = this.renderedRow;

            row.off('click');

            $('.AknIconButton--remove', row).on('click', () => {
                mediator.trigger('datagrid:unselectModel:association-product-draft-grid', this.model);
                mediator.trigger('datagrid:unselectModel:association-product-model-draft-grid', this.model);
                row.remove();
            });
        },
    });
});
