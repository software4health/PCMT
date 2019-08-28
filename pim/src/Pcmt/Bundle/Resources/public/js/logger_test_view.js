define(
    ['backbone'],
    function (BaseForm) {
        return BaseForm.extend({    //simplest Backbone view - single <div> rendered
            initialize: function(meta) {
                this.meta = meta.config;
                BaseForm.prototype.initialize.apply(this, arguments);
            },

            events: {
                'click': 'clicked'
            },
            configure: function () {
                //before rendering any actual form views, one can configure what is about to be displayed
                //for each module to get its parent or root, we can perform:
                //this.getParent() or this.getRoot()

                // return $.when(function () {
                //    return $.get('my_url').then(function (elements) {
                //        this.elements = elements;
                //    }.bind(this));
                // }, BaseForm.prototype.configure.apply(this, arguments));
            },
            render: function () {
                this.$el.html('<div>Added new frontend part!</div>');

                /**
                 * this is just a test - form data can be accessed and set  via below methods
                 */
                //let model = this.getFormData()
                // model.attribute = 'brand_new'
                //this.setData(model)
            },

            clicked: (event) => {
                console.log(event);
            }
        });
    }
);

//see Backbone View docs https://backbonejs.org/#View
//this is already a form extension and it needs to be defined and mapped in Resources/config/form_extensions.yml besides requirejs.yml


