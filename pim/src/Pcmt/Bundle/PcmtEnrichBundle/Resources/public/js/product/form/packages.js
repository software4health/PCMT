define(['pim/form'], 
    function (BaseForm) {
        return BaseForm.extend({
            configure: function () {
                this.trigger('tab:register', {
                    code: this.code,
                    isVisible: this.isVisible.bind(this),  //bind function isVisible() to current context
                    label: 'PCMT Suppliers'
                });

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            isVisible: function () {
                return true;
            },
            render: function () {
                this.$el.html('Suppliers');
                return this;
            }
        });
    }
);