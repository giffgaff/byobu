import events from './events';
import extend from './extend';
import pages from './pages';
import notifications from './notifications';
import PrivateDiscussion from "../common/models/PrivateDiscussion";
import PrivatePost from "../common/models/PrivatePost";

export * from './modals';
export * from './pages/discussions';

app.initializers.add('fof-byobu', function (app) {
    app.store.models['fof-byobu-private-discussions'] = PrivateDiscussion;
    app.store.models['fof-byobu-private-posts'] = PrivatePost;

    events(app);
    extend(app);
    pages(app);
    notifications(app);
});
