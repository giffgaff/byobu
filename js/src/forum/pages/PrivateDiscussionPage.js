import Page from 'flarum/components/Page';
import ItemList from 'flarum/utils/ItemList';
import DiscussionHero from 'flarum/components/DiscussionHero';
import DiscussionListPane from 'flarum/components/DiscussionListPane';
import PostStream from 'flarum/components/PostStream';
import PostStreamScrubber from 'flarum/components/PostStreamScrubber';
import LoadingIndicator from 'flarum/components/LoadingIndicator';
import SplitDropdown from 'flarum/components/SplitDropdown';
import listItems from 'flarum/helpers/listItems';
import DiscussionControls from 'flarum/utils/DiscussionControls';
import PostStreamState from 'flarum/states/PostStreamState';
import PrivateDiscussionListState from "../states/PrivateDiscussionListState";

export default class PrivateDiscussionPage extends Page {
    privateDiscussions = new PrivateDiscussionListState({}, app);

    oninit(vnode) {
        super.oninit(vnode);

        this.useBrowserScrollRestoration = false;

        /**
         * The discussion that is being viewed.
         *
         * @type {Discussion}
         */
        this.discussion = null;

        /**
         * The number of the first post that is currently visible in the viewport.
         *
         * @type {number}
         */
        this.near = m.route.param('near') || 0;

        this.load();

        // If the discussion list has been loaded, then we'll enable the pane (and
        // hide it by default). Also, if we've just come from another discussion
        // page, then we don't want Mithril to redraw the whole page – if it did,
        // then the pane would redraw which would be slow and would cause problems with
        // event handlers.
        if (app.discussions.hasDiscussions()) {
            app.pane.enable();
            app.pane.hide();
        }

        app.history.push('discussion');

        this.bodyClass = 'App--discussion';
    }

    onremove() {
        super.onremove();
        // If we are indeed navigating away from this discussion, then disable the
        // discussion list pane. Also, if we're composing a reply to this
        // discussion, minimize the composer – unless it's empty, in which case
        // we'll just close it.
        app.pane.disable();

        if (app.composer.composingReplyTo(this.discussion) && !app.composer.fields.content()) {
            app.composer.hide();
        } else {
            app.composer.minimize();
        }
    }

    view() {
        const discussion = this.discussion;

        return (
            <div className="DiscussionPage">
                <div className="DiscussionPage-discussion">
                    {discussion
                        ? [
                            (<header className="Hero DiscussionHero">
                                <div className="container">
                                    <ul className="DiscussionHero-items">
                                        <li><h2 className="DiscussionHero-title">{discussion.title()}</h2></li>
                                    </ul>
                                </div>
                            </header>),
                            <div className="container">
                                <nav className="DiscussionPage-nav">
                                    <ul>{listItems(this.sidebarItems().toArray())}</ul>
                                </nav>
                                <div className="DiscussionPage-stream">
                                    {PostStream.component({
                                        discussion,
                                        stream: this.stream,
                                        onPositionChange: () => {},
                                    })}
                                </div>
                            </div>,
                        ]
                        : LoadingIndicator.component({ className: 'LoadingIndicator--block' })}
                </div>
            </div>
        );
    }

    /**
     * Load the discussion from the API or use the preloaded one.
     */
    load() {
        const params = this.requestParams();
        app.store.find('fof-byobu-private-posts', m.route.param('id'), params).then(() => {
            app.store.find('fof-byobu-private-discussions', m.route.param('id'), params).then(this.show.bind(this));
        });
        m.redraw();
    }

    /**
     * Get the parameters that should be passed in the API request to get the
     * discussion.
     *
     * @return {Object}
     */
    requestParams() {
        return {
            bySlug: true,
            page: { near: this.near },
        };
    }

    /**
     * Initialize the component to display the given discussion.
     *
     * @param {Discussion} discussion
     */
    show(discussion) {
        app.history.push('discussion', discussion.title());
        app.setTitle(discussion.title());
        app.setTitleCount(0);

        // When the API responds with a discussion, it will also include a number of
        // posts. Some of these posts are included because they are on the first
        // page of posts we want to display (determined by the `near` parameter) –
        // others may be included because due to other relationships introduced by
        // extensions. We need to distinguish the two so we don't end up displaying
        // the wrong posts. We do so by filtering out the posts that don't have
        // the 'discussion' relationship linked, then sorting and splicing.
        let includedPosts = [];
        if (discussion.payload && discussion.payload.included) {
            const discussionId = discussion.id();

            includedPosts = discussion.payload.included
            .filter(
                (record) =>
                    record.type === 'posts' &&
                    record.relationships &&
                    record.relationships.discussion &&
                    record.relationships.discussion.data.id === discussionId
            )
            .map((record) => app.store.getById('fof-byobu-private-posts', record.id))
            .sort((a, b) => a.createdAt() - b.createdAt())
            .slice(0, 20);
        }

        // Set up the post stream for this discussion, along with the first page of
        // posts we want to display. Tell the stream to scroll down and highlight
        // the specific post that was routed to.
        this.stream = new PostStreamState(discussion, includedPosts);
        this.stream.goToNumber(m.route.param('near') || (includedPosts[0] && includedPosts[0].number()), true).then(() => {
            this.discussion = discussion;
        });
    }

    /**
     * Build an item list for the contents of the sidebar.
     *
     * @return {ItemList}
     */
    sidebarItems() {
        const items = new ItemList();

        items.add(
            'controls',
            SplitDropdown.component(
                {
                    icon: 'fas fa-ellipsis-v',
                    className: 'App-primaryControl',
                    buttonClassName: 'Button--primary',
                },
                DiscussionControls.controls(this.discussion, this).toArray()
            )
        );

        items.add(
            'scrubber',
            PostStreamScrubber.component({
                stream: this.stream,
                className: 'App-titleControl',
            }),
            -100
        );

        return items;
    }
}
