'use strict';

define(
    [
        'pim/form/common/attributes/attribute-group-selector'
    ],
    function (
        GroupSelectorForm
    ) {
        return GroupSelectorForm.extend({
            /**
             * {@inheritdoc}
             */
            getFormData: function () {
                return this.getRoot().model.toJSON().product;
            }
        });
    }
);
