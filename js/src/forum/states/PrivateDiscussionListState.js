import DiscussionListState from 'flarum/states/DiscussionListState';

export default class PrivateDiscussionListState extends DiscussionListState
{
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

        return this.app.store.find('private-discussions');
    }
}
