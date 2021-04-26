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
use Flarum\Api\Controller\CreatePostController;
use Flarum\Api\Controller\DeleteDiscussionController;
use Flarum\Api\Controller\DeletePostController;
use Flarum\Api\Controller\ShowDiscussionController;
use Flarum\Api\Controller\UpdateDiscussionController;
use Flarum\Api\Controller\UpdatePostController;
use Flarum\Discussion\DiscussionRepository;
use Flarum\Discussion\Search\DiscussionSearcher;
use Flarum\Foundation\AbstractServiceProvider;
use Flarum\Http\SlugManager;
use Flarum\Post\Floodgate;
use Flarum\Post\PostRepository;
use Flarum\Search\GambitManager;
use FoF\Byobu\Api\Controller\CreateByobuDiscussionController;
use FoF\Byobu\Api\Controller\CreateByobuPostController;
use FoF\Byobu\Api\Controller\DeleteByobuDiscussionController;
use FoF\Byobu\Api\Controller\DeleteByobuPostController;
use FoF\Byobu\Api\Controller\ShowByobuDiscussionController;
use FoF\Byobu\Api\Controller\UpdateByobuDiscussionController;
use FoF\Byobu\Api\Controller\UpdateByobuPostController;
use FoF\Byobu\Discussion\Screener;
use FoF\Byobu\Discussion\Search\ByobuDiscussionSearcher;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Illuminate\Contracts\Bus\Dispatcher;


class ByobuProvider extends AbstractServiceProvider
{
    private function registerDiscussionOverrides()
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

        $this->app->bind(UpdateDiscussionController::class, function($app) {
            return new UpdateByobuDiscussionController($app->make(Dispatcher::class));
        });

        $this->app->bind(DeleteDiscussionController::class, function($app) {
            return new DeleteByobuDiscussionController($app->make(Dispatcher::class));
        });
    }

    private function registerPostOverrides()
    {
        $this->app->bind(CreatePostController::class, function($app) {
            return new CreateByobuPostController(
                $app->make(Dispatcher::class),
                $app->make(Floodgate::class));
        });

        $this->app->bind(UpdatePostController::class, function($app) {
            return new UpdateByobuPostController($app->make(Dispatcher::class));
        });

        $this->app->bind(DeletePostController::class, function($app) {
            return new DeleteByobuPostController($app->make(Dispatcher::class));
        });
    }

    public function register()
    {
        $this->registerDiscussionOverrides();
        $this->registerPostOverrides();

        $this->app->bind('byobu.screener', Screener::class);
    }
}
