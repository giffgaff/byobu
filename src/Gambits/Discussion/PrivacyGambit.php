<?php

/*
 * This file is part of fof/byobu.
 *
 * Copyright (c) 2019 - 2021 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\Byobu\Gambits\Discussion;

use Flarum\Flags\Flag;
use Flarum\Search\AbstractRegexGambit;
use Flarum\Search\AbstractSearch;
use FoF\Byobu\Concerns\ExtensionsDiscovery;

class PrivacyGambit extends AbstractRegexGambit
{
    use ExtensionsDiscovery;

    /**
     * {@inheritdoc}
     */
    protected $pattern = 'is:private';

    /**
     * Apply conditions to the search, given that the gambit was matched.
     *
     * @param AbstractSearch $search  The search object.
     * @param array          $matches An array of matches from the search bit.
     * @param bool           $negate  Whether or not the bit was negated, and thus whether
     *                                or not the conditions should be negated.
     *
     * @return mixed
     */
    protected function conditions(AbstractSearch $search, array $matches, $negate)
    {
        $actor = $search->getActor();

        if ($actor->isGuest()) {
            return;
        }

        $search->getQuery()->whereIn('discussions.id', function ($query) use ($actor) {
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
