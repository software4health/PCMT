'use strict';

define(
    [
        'jquery',
        'backbone',
        'pim/base-fetcher',
        'routing',
        'oro/mediator',
        'pim/cache-invalidator'
    ],
    function (
        $,
        Backbone,
        BaseFetcher,
        Routing,
        mediator,
        CacheInvalidator
    ) {
        return BaseFetcher.extend({
            /**
             * Fetch an element based on its identifier
             *
             * @param {int} id
             *
             * @return {Promise}
             */
            fetch: function (id) {
                return $.ajax({
                    url: Routing.generate('pcmt_product_draft_api', {id: id}),
                    type: 'GET'
                })
                    .then(function (draft) {
                        return draft;
                    })
                    .promise();
            },

            /**
             * {@inheritdoc}
             */
            getIdentifierField: function () {
                return $.Deferred().resolve('id');
            }
        });
    }
);