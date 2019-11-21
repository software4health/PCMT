define(
    [
        'pim/form',
        'backbone',
        'jquery',
        'underscore',
        'oro/translator',
        'routing',
        'pcmt/product/template/draft-edit',
        'pim/dialog',
        'pim/security-context'
    ],
    function (BaseForm, Backbone, $, _, __, Routing, template, Dialog, SecurityContext) {
        return BaseForm.extend({
            template: _.template(template),
            render: function () {
                this.$el.html(this.template({}));
            }
        })
    }
);