'use strict';

define(
    [
        'pim/controller/front',
        'pim/form-builder',
        'pim/fetcher-registry'
    ],
    function (BaseController, FormBuilder, FetcherRegistry) {
        return BaseController.extend({
            renderForm: function (route) {
                return FetcherRegistry.getFetcher(this.options.config.entity).fetch(route.params.id, {cached: false})
                    .then((draft) => {

                    })
            }
        })
    }
);