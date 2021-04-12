import { extend } from 'flarum/extend';
import LinkButton from 'flarum/components/LinkButton';
import IndexPage from 'flarum/components/IndexPage';
import PrivateComposing from "./PrivateComposing";

export default (app) => {
    extend(IndexPage.prototype, 'navItems', (items) => {
        const user = app.session.user;

        if (user) {
            items.add(
                'privateDiscussions',
                LinkButton.component({
                    icon: app.forum.data.attributes['byobu.icon-badge'],
                    href: app.route('byobuPrivateDiscussionList'),
                }, app.translator.trans('fof-byobu.forum.nav.nav_item')),
                75
            );
        }
    });

    extend(IndexPage.prototype, 'setTitle', function () {
        if (app.current.get('routeName') === 'byobuPrivateDiscussionList') {
            app.setTitle(app.translator.trans('fof-byobu.forum.user.dropdown_label'));
        }
    });

    extend(IndexPage.prototype, 'sidebarItems', function (items) {
        if (app.current.get('routeName') === 'byobuPrivateDiscussionList') {
            let compose = new PrivateComposing;

            items.replace(
                'newDiscussion',
                compose.component()
            );
        }
    });
}
