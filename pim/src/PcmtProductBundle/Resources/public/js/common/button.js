'use strict'

define(['backbone'], function (Backbone) {
    return Backbone.View.extend({
        el: 'body',
        template: '<button>Create</button>',
        render: function () {
            this.el.html(this.template);
            return this;
        }
    })
});