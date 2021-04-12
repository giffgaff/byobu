import PrivateDiscussionsUserPage from "./PrivateDiscussionsUserPage";
import PrivateDiscussionsPage from "./PrivateDiscussionsPage";
import PrivateDiscussionPage from "./PrivateDiscussionPage";

export default (app) => {
    app.routes.byobuUserPrivate = { path: '/u/:username/private', component: PrivateDiscussionsUserPage };
    app.routes.byobuPrivateDiscussionList = {path: '/private', component: PrivateDiscussionsPage};
    app.routes.byobuPrivateDiscussion = {path: '/private/:id', component: PrivateDiscussionPage};

    app.route.byobuPrivateDiscussion = (discussion) => {
        return app.route('byobuPrivateDiscussion', {
            id: discussion.id()
        });

    }
}
