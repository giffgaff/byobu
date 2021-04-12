<?php

/*
 * This file is part of fof/byobu.
 *
 * Copyright (c) 2019 - 2021 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\Byobu;

use Flarum\Api\Serializer\BasicDiscussionSerializer;
use Flarum\Api\Serializer\BasicUserSerializer;
use Flarum\Api\Serializer\DiscussionSerializer;
use Flarum\Api\Serializer\ForumSerializer;
use Flarum\Api\Serializer\UserSerializer;
use Flarum\Discussion\Discussion;
use Flarum\Discussion\Event\Saving as DiscussionSaving;
use Flarum\Extend;
use Flarum\Post\Event\Saving as PostSaving;
use Flarum\User\Event\Saving as UserSaving;
use Flarum\User\User;
use FoF\Byobu\Api\Controller\ListPrivateDiscussionPostsController;
use FoF\Byobu\Api\Controller\ListPrivateDiscussionsController;
use FoF\Byobu\Api\Controller\ShowPrivateDiscussionController;
use FoF\Byobu\Content\PrivateDiscussionPage;
use FoF\Byobu\Content\PrivateDiscussionsPage;
use FoF\Components\Extend\AddFofComponents;
use FoF\Split\Events\DiscussionWasSplit;
use Illuminate\Contracts\Events\Dispatcher;

return [
    (new AddFofComponents()),

    (new Extend\Frontend('admin'))
        ->css(__DIR__.'/resources/less/admin.less')
        ->js(__DIR__.'/js/dist/admin.js'),

    (new Extend\Frontend('forum'))
        ->route('/private/{id}', 'byobuPrivateDiscussion', PrivateDiscussionPage::class)
        ->route('/private', 'byobuPrivateDiscussionList', PrivateDiscussionsPage::class)
        ->css(__DIR__.'/resources/less/forum/extension.less')
        ->js(__DIR__.'/js/dist/forum.js'),

    (new Extend\Routes('api'))
        ->get('/private-discussions', 'fof.byobu.private-discussions', ListPrivateDiscussionsController::class)
        ->get('/private-discussions/{id}', 'fof.byobu.private-discussions.get', ShowPrivateDiscussionController::class)
        ->get('/private-discussion-posts/{id}', 'fof.byobu.private-discussions.posts.get', ListPrivateDiscussionPostsController::class),

    new Extend\Locales(__DIR__.'/resources/locale'),

    (new Extend\Model(Discussion::class))
        ->relationship('recipientUsers', function ($discussion) {
            return $discussion->belongsToMany(User::class, 'recipients')
                ->withTimestamps()
                ->wherePivot('removed_at', null);
        })
        ->relationship('oldRecipientUsers', function ($discussion) {
            return $discussion->belongsToMany(User::class, 'recipients')
                ->withTimestamps()
                ->wherePivot('removed_at', '!=', null);
        }),

    (new Extend\Model(User::class))
        ->relationship('privateDiscussions', function ($user) {
            return $user->belongsToMany(Discussion::class, 'recipients')
                ->withTimestamps()
                ->wherePivot('removed_at', null);
        }),


    (new Extend\ApiController(ListPrivateDiscussionsController::class))
        ->addInclude(['recipientUsers', 'oldRecipientUsers']),

    (new Extend\ApiController(ShowPrivateDiscussionController::class))
        ->addInclude(['recipientUsers', 'oldRecipientUsers']),

    (new Extend\ApiSerializer(BasicDiscussionSerializer::class))
        ->hasMany('recipientUsers', BasicUserSerializer::class)
        ->hasMany('oldRecipientUsers', BasicUserSerializer::class),

    (new Extend\ApiSerializer(DiscussionSerializer::class))
        ->mutate(Api\DiscussionPermissionAttributes::class),

    (new Extend\ApiSerializer(ForumSerializer::class))
        ->mutate(Api\ForumPermissionAttributes::class),

    (new Extend\ApiSerializer(UserSerializer::class))
        ->hasMany('privateDiscussions', DiscussionSerializer::class)
        ->attribute('blocksPd', function ($serializer, $user) {
            return (bool) $user->blocks_byobu_pd;
        })
        ->attribute('cannotBeDirectMessaged', function ($serializer, $user) {
            return (bool) $serializer->getActor()->can('cannotBeDirectMessaged', $user);
        }),

    (new Extend\View())
        ->namespace('fof-byobu', __DIR__.'/resources/views'),

    (new Extend\Post())
        ->type(Posts\RecipientLeft::class)
        ->type(Posts\RecipientsModified::class),

    (new Extend\Notification())
        ->type(Notifications\DiscussionCreatedBlueprint::class, DiscussionSerializer::class, ['alert', 'email'])
        ->type(Notifications\DiscussionRepliedBlueprint::class, DiscussionSerializer::class, ['alert', 'email'])
        ->type(Notifications\DiscussionRecipientRemovedBlueprint::class, DiscussionSerializer::class, ['alert', 'email'])
        ->type(Notifications\DiscussionAddedBlueprint::class, DiscussionSerializer::class, ['alert', 'email']),

    (new Extend\Event())
        ->listen(DiscussionSaving::class, Listeners\PersistRecipients::class)
        ->listen(DiscussionSaving::class, Listeners\DropTagsOnPrivateDiscussions::class)
        ->listen(PostSaving::class, Listeners\IgnoreApprovals::class)
        ->listen(UserSaving::class, Listeners\SaveUserPreferences::class)
        ->listen(DiscussionWasSplit::class, Listeners\AddRecipientsToSplitDiscussion::class),

    (new Extend\ServiceProvider())
        ->register(Provider\ByobuProvider::class),

    function (Dispatcher $events) {
        $events->subscribe(Listeners\CreatePostWhenRecipientsChanged::class);
        $events->subscribe(Listeners\QueueNotificationJobs::class);
        $events->subscribe(Listeners\AddGambits::class);
    },

    (new Extend\Settings())
        ->serializeToForum('byobu.icon-badge', 'fof-byobu.icon-badge', function ($value) {
            if ($value === null || $value === '') {
                $value = 'fas fa-map';
            }

            return $value;
        })
        ->serializeToForum('byobu.icon-postAction', 'fof-byobu.icon-postAction', function ($value) {
            if ($value === null || $value === '') {
                $value = 'far fa-map';
            }

            return $value;
        }),
];
