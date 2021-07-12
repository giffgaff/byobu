<?php

/*
 * This file is part of fof/byobu.
 *
 * Copyright (c) 2019 - 2021 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\Byobu\Listeners;

use Flarum\Post\Event\Saving;
use Flarum\User\Exception\PermissionDeniedException;
use FoF\Byobu\Discussion\Screener;

class CheckForBlockedPd
{
    public function handle(Saving $event)
    {
        /** @var Screener $screener */
        $screener = app('byobu.screener');
        $screener = $screener->fromDiscussion($event->post->discussion);

        if ($screener->hasBlockingUsers()) {
            throw new PermissionDeniedException('Not allowed to add users that blocked receiving private discussions');
        }
    }
}
