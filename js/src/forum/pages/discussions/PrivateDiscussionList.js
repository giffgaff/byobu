import DiscussionList from 'flarum/components/DiscussionList';
import PrivateDiscussionListItem from "./PrivateDiscussionListItem";

export default class PrivateDiscussionList extends DiscussionList {
    view() {
        const state = this.attrs.state;

        const params = state.getParams();

        if (state.empty()) {
            const text = app.translator.trans('core.forum.discussion_list.empty_text');
            return <div className="DiscussionList">{Placeholder.component({ text })}</div>;
        }

        return (
            <div className={'DiscussionList' + (state.isSearchResults() ? ' DiscussionList--searchResults' : '')}>
                <ul className="DiscussionList-discussions">
                    {state.discussions.map((discussion) => {
                        return (
                            <li key={discussion.id()} data-id={discussion.id()}>
                                {PrivateDiscussionListItem.component({ discussion, params })}
                            </li>
                        );
                    })}
                </ul>
            </div>
        );
    }
}
