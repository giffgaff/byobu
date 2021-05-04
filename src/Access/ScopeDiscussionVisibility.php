<?php

/*
 * This file is part of fof/byobu.
 *
 * Copyright (c) 2019 - 2021 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\Byobu\Access;

use Flarum\Flags\Flag;
use Flarum\User\User;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use FoF\Byobu\Concerns\ExtensionsDiscovery;

class ScopeDiscussionVisibility
{
    use ExtensionsDiscovery;

    /**
     * @param User            $actor
     * @param EloquentBuilder $query
     */
    public function __invoke(User $actor, EloquentBuilder $query)
    {
        if ($actor->isGuest()) {
            return;
        }

        if ($actor->getAttribute('bypassByobuScopeDiscussionVisibility')) {
            return;
        }

        $query->orWhereIn('discussions.id', function ($query) use ($actor) {
            $query->select('recipients.discussion_id')
                ->from('recipients')
                ->whereNull('recipients.removed_at')
                ->whereIn('recipients.user_id', [$actor->id]);

            if ($this->canViewFlaggedPrivateDiscussions($actor)) {
                $query->orWhereIn('recipients.discussion_id', function ($query) {
                    $query->select('posts.discussion_id')
                        ->from('posts')
                        ->join('flags', 'flags.post_id', 'posts.id');
                });
            }
        });
    }

    private function canViewFlaggedPrivateDiscussions($actor): bool
    {
        return $this->flagsInstalled()
            && $actor->hasPermission('user.viewPrivateDiscussionsWhenFlagged')
            && $actor->hasPermission('discussion.viewFlags');
    }

    protected function flagsInstalled(): bool
    {
        return $this->extensionIsEnabled('flarum-flags') && class_exists(Flag::class);
    }
}
