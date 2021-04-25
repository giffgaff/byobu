<?php

/*
 * This file is part of fof/byobu.
 *
 * Copyright (c) 2019 - 2021 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\Byobu\Provider;

use Flarum\Api\Controller\CreateDiscussionController;
use Flarum\Api\Controller\ShowDiscussionController;
use Flarum\Discussion\DiscussionRepository;
use Flarum\Discussion\Search\DiscussionSearcher;
use Flarum\Foundation\AbstractServiceProvider;
use Flarum\Http\SlugManager;
use Flarum\Post\Floodgate;
use Flarum\Post\PostRepository;
use Flarum\Search\GambitManager;
use FoF\Byobu\Api\Controller\CreateByobuDiscussionController;
use FoF\Byobu\Api\Controller\ShowByobuDiscussionController;
use FoF\Byobu\Discussion\Screener;
use FoF\Byobu\Discussion\Search\ByobuDiscussionSearcher;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Illuminate\Contracts\Bus\Dispatcher;


class ByobuProvider extends AbstractServiceProvider
{
    public function register()
    {
        $this->app->bind( DiscussionSearcher::class, function ($app) {
            return new ByobuDiscussionSearcher(
                $app->make(GambitManager::class),
                $app->make(DiscussionRepository::class),
                $app->make(EventDispatcher::class));
        });

        $this->app->bind(ShowDiscussionController::class, function($app) {
            return new ShowByobuDiscussionController(
                $app->make(DiscussionRepository::class),
                $app->make(PostRepository::class),
                $app->make(SlugManager::class));
        });

        $this->app->bind(CreateDiscussionController::class, function($app) {
            return new CreateByobuDiscussionController(
                $app->make(Dispatcher::class),
                $app->make(Floodgate::class));
        });

        $this->app->bind('byobu.screener', Screener::class);
    }
}
