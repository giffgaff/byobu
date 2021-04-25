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

use Flarum\Event\ConfigureDiscussionGambits;
use Flarum\Event\ConfigureUserGambits;
use FoF\Byobu\Gambits\Discussion\PrivacyGambit;
use FoF\Byobu\Gambits\User\AllowsPdGambit;
use Illuminate\Contracts\Events\Dispatcher;

class AddGambits
{
    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(ConfigureUserGambits::class, [$this, 'addUserGambits']);
    }

    public function addUserGambits(ConfigureUserGambits $event)
    {
        $event->gambits->add(AllowsPdGambit::class);
    }
}
