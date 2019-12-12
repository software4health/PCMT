'use strict';

define(
    [
        'pim/form',
        'backbone',
        'jquery',
        'underscore',
        'oro/translator',
        'routing',
        'pcmt/product/template/draft-pagination',
        'pim/security-context',
        'pim/router'
    ],
    function (BaseForm, Backbone, $, _, __, Routing, template, SecurityContext, Router) {
        return BaseForm.extend({
            template: _.template(template),
            events: {
                "click a": "onChangePage"
            },

            configure: function () {
                this.listenTo(this.getRoot(), 'pcmt:drafts:listReloaded', this.render);
            },

            getPaginationHandles: function (model) {

                if (null == model) {
                    return;
                }

                let handles = [];

                const _firstPage = model.firstPage;
                const _currentPage = model.currentPage;
                const _lastPage = model.lastPage;

                if (_lastPage === 0) {
                    return [];
                }

                // how many pages from current should we see in pagination
                const diff = (_currentPage === _firstPage || _currentPage === _lastPage) ? 2 : 1;

                for (let i = _firstPage; i <= _lastPage; ++i) {
                    if (i === _firstPage || i === _lastPage) {
                        handles.push({
                            pageNo: i,
                            className: _currentPage === i ? 'active AknActionButton--highlight' : undefined
                        });
                        continue;
                    }
                    if (i < _currentPage - diff) {
                        handles.push({
                            pageNo: '...',
                            className: 'AknActionButton--unclickable'
                        });
                        i = _currentPage - diff - 1;
                        continue;
                    }
                    if (i <= _currentPage + diff) {
                        handles.push({
                            pageNo: i,
                            className: _currentPage === i ? 'active AknActionButton--highlight' : undefined
                        });
                        continue;
                    }
                    if (i > _currentPage + diff) {
                        handles.push({
                            pageNo: '...',
                            className: 'AknActionButton--unclickable'
                        });

                        i = _lastPage - 1;
                        continue;
                    }
                }

                return handles;
            },

            render: function () {

                let model = this.getFormData();
                this.$el.empty();
                if (!model.draftsData.params.lastPage) {
                    return;
                }
                let handles = this.getPaginationHandles(model.draftsData.params);
                this.$el.html(this.template({
                    handles: handles,
                }));
            },

            onChangePage: function (e) {
                const model = this.getFormData();
                let a = $(e.currentTarget);
                let $span = a.find('.js-page-change');
                const page = $span.data('page');
                model.draftsData.params.currentPage = page;
                this.setData(model);
                this.render();
                this.getRoot().trigger('pcmt:form:entity:update_pagination');
            }
        });
    }
);

