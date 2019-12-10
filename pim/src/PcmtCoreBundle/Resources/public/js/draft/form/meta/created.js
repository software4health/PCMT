'use strict';

define(
    [
        'underscore',
        'pcmt/draft/common/created',
        'pim/template/product/meta/created'
    ],
    function (_, Created, template) {
        return Created.extend({
            className: 'AknColumn-block',

            template: _.template(template)
        });
    }
);
