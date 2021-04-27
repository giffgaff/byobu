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

use Flarum\Api\Controller;
use Flarum\Api\Serializer;
use Flarum\Discussion\Discussion;
use Flarum\Discussion\Event\Saving as DiscussionSaving;
use Flarum\Event\GetModelIsPrivate;
use Flarum\Extend;
use Flarum\Flags\Api\Controller\CreateFlagController;
use Flarum\Flags\Api\Controller\DeleteFlagsController;
use Flarum\Flags\Api\Controller\ListFlagsController;
use Flarum\Post\Event\Saving as PostSaving;
use Flarum\User\Event\Saving as UserSaving;
use Flarum\User\User;
use FoF\Byobu\Access\ScopeDiscussionVisibility;
use FoF\Components\Extend\AddFofComponents;
use FoF\Split\Events\DiscussionWasSplit;
use Illuminate\Contracts\Events\Dispatcher;

return [
    (new AddFofComponents()),

    (new Extend\Frontend('admin'))
        ->css(__DIR__.'/resources/less/admin.less')
        ->js(__DIR__.'/js/dist/admin.js'),

    (new Extend\Frontend('forum'))
        ->route('/private', 'byobuPrivate', Content\PrivateDiscussionsPage::class)
        ->css(__DIR__.'/resources/less/forum/extension.less')
        ->js(__DIR__.'/js/dist/forum.js'),

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

    (new Extend\ApiController(Controller\ListDiscussionsController::class))
        ->addInclude(['recipientUsers', 'oldRecipientUsers']),

    (new Extend\ApiController(Controller\ShowDiscussionController::class))
        ->addInclude(['recipientUsers', 'oldRecipientUsers'])
        ->prepareDataQuery(function($controller) {
            Discussion::registerVisibilityScoper(new ScopeDiscussionVisibility(), 'view');
        }),

    (new Extend\ApiController(Controller\CreateDiscussionController::class))
        ->prepareDataQuery(function($controller) {
            Discussion::registerVisibilityScoper(new ScopeDiscussionVisibility(), 'view');
        }),

    (new Extend\ApiController(Controller\UpdateDiscussionController::class))
        ->prepareDataQuery(function($controller) {
            Discussion::registerVisibilityScoper(new ScopeDiscussionVisibility(), 'view');
        }),

    (new Extend\ApiController(Controller\DeleteDiscussionController::class))
        ->prepareDataQuery(function($controller) {
            Discussion::registerVisibilityScoper(new ScopeDiscussionVisibility(), 'view');
        }),

    (new Extend\ApiController(Controller\CreatePostController::class))
        ->prepareDataQuery(function($controller) {
            Discussion::registerVisibilityScoper(new ScopeDiscussionVisibility(), 'view');
        }),

    (new Extend\ApiController(Controller\UpdatePostController::class))
        ->prepareDataQuery(function($controller) {
            Discussion::registerVisibilityScoper(new ScopeDiscussionVisibility(), 'view');
        }),

    (new Extend\ApiController(Controller\DeletePostController::class))
        ->prepareDataQuery(function($controller) {
            Discussion::registerVisibilityScoper(new ScopeDiscussionVisibility(), 'view');
        }),

    (new Extend\ApiController(Controller\ListNotificationsController::class))
        ->prepareDataQuery(function($controller) {
            Discussion::registerVisibilityScoper(new ScopeDiscussionVisibility(), 'view');
        }),

    (new Extend\ApiController(ListFlagsController::class))
        ->prepareDataQuery(function($controller) {
            Discussion::registerVisibilityScoper(new ScopeDiscussionVisibility(), 'view');
        }),

    (new Extend\ApiController(CreateFlagController::class))
        ->prepareDataQuery(function($controller) {
            Discussion::registerVisibilityScoper(new ScopeDiscussionVisibility(), 'view');
        }),

    (new Extend\ApiController(DeleteFlagsController::class))
        ->prepareDataQuery(function($controller) {
            Discussion::registerVisibilityScoper(new ScopeDiscussionVisibility(), 'view');
        }),

    (new Extend\ApiSerializer(Serializer\BasicDiscussionSerializer::class))
        ->hasMany('recipientUsers', Serializer\BasicUserSerializer::class)
        ->hasMany('oldRecipientUsers', Serializer\BasicUserSerializer::class),

    (new Extend\ApiSerializer(Serializer\DiscussionSerializer::class))
        ->mutate(Api\DiscussionPermissionAttributes::class),

    (new Extend\ApiSerializer(Serializer\ForumSerializer::class))
        ->mutate(Api\ForumPermissionAttributes::class),

    (new Extend\ApiSerializer(Serializer\UserSerializer::class))
        ->hasMany('privateDiscussions', Serializer\DiscussionSerializer::class)
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
        ->type(Notifications\DiscussionCreatedBlueprint::class, Serializer\DiscussionSerializer::class, ['alert', 'email'])
        ->type(Notifications\DiscussionRepliedBlueprint::class, Serializer\DiscussionSerializer::class, ['alert', 'email'])
        ->type(Notifications\DiscussionRecipientRemovedBlueprint::class, Serializer\DiscussionSerializer::class, ['alert', 'email'])
        ->type(Notifications\DiscussionAddedBlueprint::class, Serializer\DiscussionSerializer::class, ['alert', 'email']),

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

        // Listeners for old-style events, will be removed in future betas
        $events->listen(GetModelIsPrivate::class, Listeners\GetModelIsPrivate::class);
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
