'use strict';

define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/user-context',
        'pim/router',
        'pim/form'
    ],
    function (
        $,
        _,
        __,
        UserContext,
        router,
        BaseForm
    ) {
        return BaseForm.extend({
            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:field:extension:add', this.addFieldExtension);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            addFieldExtension: function (event) {
                event.field.setEditable(false);

                return this;
            }
        });
    }
);
