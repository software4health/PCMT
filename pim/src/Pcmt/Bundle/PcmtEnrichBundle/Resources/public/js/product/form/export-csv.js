'use strict';

define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'pcmt_csv_exporter_template',
        'routing'
    ],
    function (
        _,
        __,
        BaseForm,
        template,
        Routing
    ) {
        return BaseForm.extend({
            template: _.template(template),
            render: function () {
                this.$el.html(
                    this.template({
                        path: Routing.generate('pcmt_csv_product_export', {id: this.getFormData().meta.id}),
                        label: __('pim_enrich.entity.product.btn.csv_export')
                    })
                );

                return this;
            }
        });
    }
);