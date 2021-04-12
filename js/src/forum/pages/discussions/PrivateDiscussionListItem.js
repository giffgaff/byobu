import Link from 'flarum/components/Link';
import avatar from 'flarum/helpers/avatar';
import listItems from 'flarum/helpers/listItems';
import highlight from 'flarum/helpers/highlight';
import icon from 'flarum/helpers/icon';
import humanTime from 'flarum/utils/humanTime';
import abbreviateNumber from 'flarum/utils/abbreviateNumber';
import Dropdown from 'flarum/components/Dropdown';
import DiscussionControls from 'flarum/utils/DiscussionControls';
import extractText from 'flarum/utils/extractText';
import DiscussionListItem from 'flarum/components/DiscussionListItem';

export default class PrivateDiscussionListItem extends DiscussionListItem {
    view() {
        const discussion = this.attrs.discussion;
        const user = discussion.user();
        const isUnread = discussion.isUnread();
        const isRead = discussion.isRead();
        const showUnread = !this.showRepliesCount() && isUnread;
        let jumpTo = 0;
        const controls = DiscussionControls.controls(discussion, this).toArray();
        const attrs = this.elementAttrs();

        const post = discussion.mostRelevantPost();

        return (
            <div {...attrs}>
                {controls.length
                    ? Dropdown.component(
                        {
                            icon: 'fas fa-ellipsis-v',
                            className: 'DiscussionListItem-controls',
                            buttonClassName: 'Button Button--icon Button--flat Slidable-underneath Slidable-underneath--right',
                        },
                        controls
                    )
                    : ''}

                <span
                    className={'Slidable-underneath Slidable-underneath--left Slidable-underneath--elastic' + (isUnread ? '' : ' disabled')}
                    onclick={this.markAsRead.bind(this)}
                >
          {icon('fas fa-check')}
        </span>

                <div className={'DiscussionListItem-content Slidable-content' + (isUnread ? ' unread' : '') + (isRead ? ' read' : '')}>
                    <Link
                        href={user ? app.route.user(user) : '#'}
                        className="DiscussionListItem-author"
                        title={extractText(
                            app.translator.trans('core.forum.discussion_list.started_text', { user: user, ago: humanTime(discussion.createdAt()) })
                        )}
                        oncreate={function (vnode) {
                            $(vnode.dom).tooltip({ placement: 'right' });
                        }}
                    >
                        {avatar(user, { title: '' })}
                    </Link>

                    <ul className="DiscussionListItem-badges badges">{listItems(discussion.badges().toArray())}</ul>

                    <Link href={app.route.byobuPrivateDiscussion(discussion)} className="DiscussionListItem-main">
                        <h3 className="DiscussionListItem-title">{highlight(discussion.title(), this.highlightRegExp)}</h3>
                        <ul className="DiscussionListItem-info">{listItems(this.infoItems().toArray())}</ul>
                    </Link>

                    <span
                        className="DiscussionListItem-count"
                        onclick={this.markAsRead.bind(this)}
                        title={showUnread ? app.translator.trans('core.forum.discussion_list.mark_as_read_tooltip') : ''}
                    >
            {abbreviateNumber(discussion[showUnread ? 'unreadCount' : 'replyCount']())}
          </span>
                </div>
            </div>
        );
    }
}
