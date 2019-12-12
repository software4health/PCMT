'use strict';
define(
    [
        'pim/form',
        'oro/translator',
    ], function (
        BaseForm,
        __
    ) {
        return BaseForm.extend({
            count: null,

            initialize(config) {
                this.config = config.config;
            },

            configure: function () {
                this.listenTo(this.getRoot(), 'pcmt:drafts:listReloaded', this.setupCount.bind(this));
            },

            render() {
                if (null !== this.count) {
                    this.$el.text(
                        __(this.config.title, {count: this.count}, this.count)
                    );
                } else if (false === this.config.countable) {
                    this.$el.text(
                        __(this.config.title)
                    );
                }
            },

            setupCount() {
                let model = this.getFormData();
                this.count = model.draftsData.params.total;
                this.render();
            }
        });
    }
);
