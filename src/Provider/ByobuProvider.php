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

use Flarum\Api\Controller\DeleteDiscussionController;
use Flarum\Api\Controller\DeletePostController;
use Flarum\Discussion\Search\DiscussionSearcher;
use Flarum\Discussion\Search\Gambit\AuthorGambit;
use Flarum\Discussion\Search\Gambit\CreatedGambit;
use Flarum\Discussion\Search\Gambit\FulltextGambit as DiscussionFulltextGambit;
use Flarum\Discussion\Search\Gambit\HiddenGambit;
use Flarum\Discussion\Search\Gambit\UnreadGambit;
use Flarum\Event\ConfigureDiscussionGambits;
use Flarum\Flags\Api\Controller\DeleteFlagsController;
use Flarum\Foundation\AbstractServiceProvider;
use Flarum\Search\GambitManager;
use FoF\Byobu\Api\Overrides\DeleteDiscussionControllerOverride;
use FoF\Byobu\Api\Overrides\DeleteFlagsControllerOverride;
use FoF\Byobu\Api\Overrides\DeletePostControllerOverride;
use FoF\Byobu\Discussion\Screener;
use FoF\Byobu\Discussion\Search\ByobuDiscussionSearcher;
use Illuminate\Contracts\Container\Container;

class ByobuProvider extends AbstractServiceProvider
{
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
        $this->app->bind(DiscussionSearcher::class, ByobuDiscussionSearcher::class);
        $this->app->bind(DeleteDiscussionController::class, DeleteDiscussionControllerOverride::class);
        $this->app->bind(DeletePostController::class, DeletePostControllerOverride::class);
        $this->app->bind(DeleteFlagsController::class, DeleteFlagsControllerOverride::class);

        $this->app->bind('byobu.screener', Screener::class);
    }
}
