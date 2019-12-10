'use strict';

define(
    ['pim/product-model-edit-form/product-model-label'],
    function (Label) {
        return Label.extend({
            /**
             * {@inheritdoc}
             */
            getFormData: function () {
                return this.getRoot().model.toJSON().product;
            }
        });
    }
);