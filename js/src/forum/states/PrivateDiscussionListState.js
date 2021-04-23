import DiscussionListState from 'flarum/states/DiscussionListState';

export default class PrivateDiscussionListState extends DiscussionListState
{

    /**
     * Clear and reload the discussion list. Passing the option `deferClear: true`
     * will clear discussions only after new data has been received.
     * This can be used to refresh discussions without loading animations.
     */
    refresh({ deferClear = false } = {}) {
      this.loading = true;

      if (!deferClear) {
        this.clear();
      }

      return this.loadResults().then(
        (results) => {
          // This ensures that any changes made while waiting on this request
          // are ignored. Otherwise, we could get duplicate discussions.
          // We don't use `this.clear()` to avoid an unnecessary redraw.
          this.discussions = [];
          this.parseResults(results);
        },
        () => {
          this.loading = false;
          m.redraw();
        }
      );
    }

    /**
     * Load a new page of discussion results.
     *
     * @param offset The index to start the page at.
     */
    loadResults(offset) {
        const preloadedDiscussions = this.app.preloadedApiDocument();

        if (preloadedDiscussions) {
            return Promise.resolve(preloadedDiscussions);
        }

        const params = this.requestParams();
        params.page = 4;
        params.include = params.include.join(',');

        return this.app.store.find('fof-byobu-private-discussions', params);
    }

     /**
    * Load the next page of discussion results.
     */
    loadMore() {
      this.loading = true;

      this.loadResults(this.discussions.length).then(this.parseResults.bind(this));
    }
}
