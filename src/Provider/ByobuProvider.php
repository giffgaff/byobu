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
use Flarum\Discussion\Search\Gambit\AuthorGambit;
use Flarum\Discussion\Search\Gambit\CreatedGambit;
use Flarum\Discussion\Search\Gambit\FulltextGambit as DiscussionFulltextGambit;
use Flarum\Discussion\Search\Gambit\HiddenGambit;
use Flarum\Discussion\Search\Gambit\UnreadGambit;
use Flarum\Event\ConfigureDiscussionGambits;
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
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Illuminate\Contracts\Bus\Dispatcher;


class ByobuProvider extends AbstractServiceProvider
{
    private function registerDiscussionOverrides()
    {
        $this->app->bind( DiscussionSearcher::class, function ($app) {
            return $app->make(ByobuDiscussionSearcher::class);
        });

        $this->app->bind(ShowDiscussionController::class, function($app) {
            return $app->make(ShowByobuDiscussionController::class);
        });

        $this->app->bind(CreateDiscussionController::class, function($app) {
            return $app->make(CreateByobuDiscussionController::class);
        });

        $this->app->bind(UpdateDiscussionController::class, function($app) {
            return $app->make(UpdateByobuDiscussionController::class);
        });

        $this->app->bind(DeleteDiscussionController::class, function($app) {
            return $app->make(DeleteByobuDiscussionController::class);
        });
    }

    private function registerPostOverrides()
    {
        $this->app->bind(CreatePostController::class, function($app) {
            return $app->make(CreateByobuPostController::class);
        });

        $this->app->bind(UpdatePostController::class, function($app) {
            return $app->make(UpdateByobuPostController::class);
        });

        $this->app->bind(DeletePostController::class, function($app) {
            return $app->make(DeleteByobuPostController::class);
        });
    }

    private function fixGambits()
    {
        $this->app->when(ByobuDiscussionSearcher::class)
            ->needs(GambitManager::class)
            ->give(function (Container $app) {
                $gambits = new GambitManager($app);

                $gambits->setFulltextGambit(DiscussionFulltextGambit::class);
                $gambits->add(AuthorGambit::class);
                $gambits->add(CreatedGambit::class);
                $gambits->add(HiddenGambit::class);
                $gambits->add(UnreadGambit::class);

                $app->make('events')->dispatch(
                    new ConfigureDiscussionGambits($gambits)
                );

                return $gambits;
            });
    }

    public function register()
    {
        $this->fixGambits();
        $this->registerDiscussionOverrides();
        $this->registerPostOverrides();

        $this->app->bind('byobu.screener', Screener::class);
    }
}
