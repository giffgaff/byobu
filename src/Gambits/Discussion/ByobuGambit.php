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

use Flarum\Discussion\Search\DiscussionSearch;
use Flarum\Search\AbstractRegexGambit;
use Flarum\Search\AbstractSearch;
use Flarum\User\User;
use Flarum\User\UserRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use LogicException;

class ByobuGambit extends AbstractRegexGambit
{
    /**
     * {@inheritdoc}
     */
    protected $pattern = 'byobu:(.+)';

    /**
     * @var UserRepository
     */
    protected $users;

    /**
     * @param UserRepository $users
     */
    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

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
        if (!$search instanceof DiscussionSearch) {
            throw new LogicException('This gambit can only be applied on a DiscussionSearch');
        }

        $usernames = explode(',', trim($matches[1], '"'));

        $actor = $search->getActor();

        /** @var Collection|User[] $users */
        $users = User::query()
            ->whereIn('username', $usernames)
            ->whereVisibleTo($actor)
            ->get();
        
        /** @var array|int[] $userIds */
        $userIds = $users
            ->pluck('id')
            ->toArray();

        $search->getQuery()
            ->distinct()
            ->join('recipients', 'discussions.id', '=', 'recipients.discussion_id')
            ->whereIn('recipients.user_id', $userIds);
    }
}
