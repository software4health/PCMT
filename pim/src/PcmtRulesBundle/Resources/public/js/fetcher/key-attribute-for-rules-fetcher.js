'use strict';

define(['jquery', 'underscore', 'pim/base-fetcher', 'routing'], function($, _, BaseFetcher, Routing) {
  return BaseFetcher.extend({

    /**
     * Fetch all elements of the collection
     *
     * @return {Promise}
     */
    fetchForFamilies: function (sourceFamily, destinationFamily) {
      let searchOptions = {
        sourceFamily: sourceFamily,
        destinationFamily: destinationFamily
      };
      return this.search(searchOptions);
    },

  });
});
