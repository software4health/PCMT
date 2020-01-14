/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

/**
 * Extension to display the variant navigation to browse variant product structure (parents and children)
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'pim/router',
        'pim/product-edit-form/variant-navigation'
    ],
    function (
        router,
        BaseForm
    ) {
        return BaseForm.extend({
            /**
             * Redirect the user to the given entity edit page
             *
             * @param {Object} entity
             */
            redirectToEntity: function (entity) {
                if (!entity) {
                    return;
                }

                let params = {};
                let route = '';

                if ('draft' === entity.model_type) {
                    route = 'pcmt_core_drafts_index';
                } else {
                    params = {id: entity.id};
                    route = ('product_model' === entity.model_type)
                        ? 'pim_enrich_product_model_edit'
                        : 'pim_enrich_product_edit'
                    ;
                }

                router.redirectToRoute(route, params);
            }
        });
    }
);
