import User from 'flarum/core/models/User';

import { extend } from 'flarum/extend';
import addPrivateDiscussionPermission from './addPrivateDiscussionPermission';
import addSettingsModal from "./addSettingsModal";
import PermissionGrid from 'flarum/components/PermissionGrid';

app.initializers.add('fof-byobu', app => {
  app.store.models.recipients = User;

  addPrivateDiscussionPermission();
  addSettingsModal();

  extend(PermissionGrid.prototype, 'moderateItems', items => {
    items.add('actor-can-view-private-discussions-when-flagged', {
      icon: 'fas fa-flag',
      label: app.translator.trans('fof-byobu.admin.permission.actor_can_view_private_discussions-when-flagged'),
      permission: 'user.actorCanViewPrivateDiscussionsWhenFlagged'
    }, 1);
  });
  
  extend(PermissionGrid.prototype, 'moderateItems', items => {
    items.add('actor-can-view-only-flagged-posts', {
      icon: 'fas fa-flag',
      label: app.translator.trans('fof-byobu.admin.permission.actor_can_view_only_flagged_posts'),
      permission: 'user.actorCanViewOnlyFlaggedPosts'
    }, 1);
  });
});
