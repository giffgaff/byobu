import ItemList from 'flarum/utils/ItemList';
import listItems from 'flarum/helpers/listItems';
import PrivateDiscussionList from './discussions/PrivateDiscussionList';
import Button from 'flarum/components/Button';
import PrivateDiscussionListState from "../states/PrivateDiscussionListState";
import IndexPage from "flarum/components/IndexPage";


export default class PrivateDiscussionsPage extends IndexPage {
    privateDiscussions = new PrivateDiscussionListState({}, app);

    oninit(vnode) {
        super.oninit(vnode);

        if (app.previous.matches(PrivateDiscussionsPage)) {
            this.privateDiscussions.clear();
        }

        this.privateDiscussions.refreshParams(app.search.params());
    }

    view() {
        return (
            <div className="IndexPage">
                {this.hero()}
                <div className="container">
                    <div className="sideNavContainer">
                        <nav className="IndexPage-nav sideNav">
                            <ul>{listItems(this.sidebarItems().toArray())}</ul>
                        </nav>
                        <div className="IndexPage-results sideNavOffset">
                            <div className="IndexPage-toolbar">
                                <ul className="IndexPage-toolbar-view">{listItems(this.viewItems().toArray())}</ul>
                                <ul className="IndexPage-toolbar-action">{listItems(this.actionItems().toArray())}</ul>
                            </div>
                            <PrivateDiscussionList state={this.privateDiscussions} />
                        </div>
                    </div>
                </div>
            </div>
        );
    }

    viewItems() {
        const items = new ItemList();
        items.remove('sort');
        return items;
    }

    actionItems() {
        const items = IndexPage.prototype.actionItems();

        items.add(
            'refresh',
            Button.component({
                title: app.translator.trans('core.forum.index.refresh_tooltip'),
                icon: 'fas fa-sync',
                className: 'Button Button--icon',
                onclick: () => {
                    this.privateDiscussions.refresh();
                    if (app.session.user) {
                        app.store.find('users', app.session.user.id());
                        m.redraw();
                    }
                },
            })
        );
        return items;
    }
}
