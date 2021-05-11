<?php

/*
 * This file is part of fof/byobu.
 *
 * Copyright (c) 2019 - 2021 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\Byobu\Discussion;

use Flarum\Discussion\Discussion;
use Flarum\Discussion\Event\Saving;
use Flarum\User\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

/**
 * @property Saving|null        $event
 * @property Collection|User[]  $currentUsers
 * @property Collection|User[]  $users
 */
class Screener extends Fluent
{
    public function fromDiscussion(Discussion $discussion): Screener
    {
        $screener = new self();

        $screener->users = $screener->currentUsers = $discussion->recipientUsers()->get();

        return $screener;
    }

    public function whenSavingDiscussions(Saving $event): Screener
    {
        $screener = new self();
        $screener->currentUsers = $event->discussion->recipientUsers()->get();

        $screener->users = static::getRecipientsFromPayload($event, 'users');

        $screener->event = $event;

        return $screener;
    }

    public function actor(): ?User
    {
        return $this->event->actor ?? null;
    }

    public function nothingChanged(): bool
    {
        $nothingChanged = true;

        foreach (['added', 'deleted'] as $action) {
            if ($this->{$action}('users')->isNotEmpty()) {
                return false;
            }
        }

        return $nothingChanged;
    }

    public function isPrivate(): bool
    {
        return $this->users->isNotEmpty();
    }

    public function wasPrivate(): bool
    {
        return $this->currentUsers->isNotEmpty();
    }

    protected function getRecipientsFromPayload(Saving $event, string $type): Collection
    {
        $ids = collect(Arr::get(
            $event->data,
            'relationships.'.static::relationName($type).'.data',
            []
        ))->pluck('id');

        return new Collection($ids->map(function($id) {
          return User::query()->findOrFail($id);
        }));
    }

    final public static function relationName(string $type)
    {
        return 'recipient'.Str::ucfirst($type);
    }

    public function hasBlockingUsers(): bool
    {
        return $this->users
            ->first(function (User $user) {
                return $user->getPreference('blocksPd', false);
            }) !== null;
    }

    public function deleted(string $type)
    {
        return $this->currentUsers->diff($this->users);
    }

    public function added(string $type)
    {
        return $this->users->diff($this->currentUsers);
    }

    public function actorRemoved(): bool
    {
        return $this->deleted('users')->find($this->actor()) !== null;
    }

    public function onlyActorRemoved(): bool
    {
        // Actor hasn't been removed.
        if (!$this->actorRemoved()) {
            return false;
        }
        // More than just the actor removed.
        if ($this->deleted('users')->count() > 1) {
            return false;
        }
        // Users were added.
        if ($this->added('users')->count() > 0) {
            return false;
        }

        return true;
    }
}
