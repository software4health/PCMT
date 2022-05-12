'use strict';

define(['jquery', 'underscore', 'oro/translator', 'pim/form', 'fhir/template/attribute/tab/additional'],
    function ($, _, __, BaseForm, template) {
        return BaseForm.extend({
            events: {
                "change select": "updateModelAfterChange",
            },
            template: _.template(template),
            mapping:'',
            mapping_response: '',
            configure: function () {
                this.trigger('tab:register', {
                    code: this.code,
                    label: __('pcmt_core.fhir.tab.lable')
                });
                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                if(this.getFormData().code !== null && this.getFormData().type !== null){
                    //get mapping
                    this.getCurrentMapping(this.getFormData().code,this.getFormData().type);
                }
                let selected=this.mapping;
                this.$el.html(this.template({
                    label: __('pcmt_core.fhir.select.lable'),
                    options: [{"value":"description","label":__("fhir.options.description")},{"value":"identifier","label":__("fhir.options.identifier")}],
                    selected: selected,
                    title: __('pcmt_core.fhir.title.lable'),
                    placeholder: __('pcmt_core.fhir.placeholder.lable'),
                    save_success: __('pcmt_core.fhir.saved.lable'),
                    save_failed: __('pcmt_core.fhir.failed.lable')
                }));
                this.$('.select2').select2();
                this.delegateEvents();
                return this;
            },
            updateModelAfterChange: function (event) {
                let value=event.target.value;
                this.updateFhirMapping(this.getFormData().code,this.getFormData().type,value);
                this.mapping=value;
                this.render();
            },
            updateFhirMapping: function (code,type,mapping){
                $.ajax(
                    {
                        url: Routing.generate('pim_fhir_fhir_create'),
                        type: 'POST',
                        data: JSON.stringify({
                            code: code,
                            type: type,
                            mapping: mapping
                        })
                    }
                ).done(function(resp) {
                    console.log(resp);
                    this.$('#success_div').hide();
                    this.$('#error_div').hide();
                    this.$('#error').html('');
                    if(resp.success === true){
                        this.$('#success_div').show();
                    }else{
                        this.$('#error').html(resp.error);
                        this.$('#error_div').show();
                    }
                }.bind(this));

            },
            getCurrentMapping: function (code,type){
                $.ajax(
                    {
                        url: Routing.generate('pim_fhir_get_mapping'),
                        type: 'POST',
                        data: JSON.stringify({
                            code: code,
                            type: type
                        })
                    }
                ).done(function (resp){
                    this.mapping=resp.mapping;
                }.bind(this));

            }


        });
    }
);