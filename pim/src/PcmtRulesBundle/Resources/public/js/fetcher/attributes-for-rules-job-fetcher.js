'use strict';

define(['jquery', 'underscore', 'pim/base-fetcher', 'routing'], function($, _, BaseFetcher, Routing) {
  return BaseFetcher.extend({

    /**
     * Fetch all elements of the collection
     *
     * @return {Promise}
     */
    fetchForFamily: function (family) {
      let searchOptions = {
        family: family,
      };
      return this.search(searchOptions);
    },

  });
});
