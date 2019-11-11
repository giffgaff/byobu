<?php


namespace FoF\Byobu\Access;

use Flarum\Post\Post;
use Flarum\User\AbstractPolicy;
use Flarum\User\User;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;

class PostPolicy extends AbstractPolicy
{
    protected $model = Post::class;

    public function findPrivate(User $actor, EloquentBuilder $query) {
if ($actor->hasPermission('user.actorCanViewOnlyFlaggedPosts')) {
        $query->whereExists(function ($query) use ($actor) {
               return $query->selectRaw('1')
            ->from('discussions')
            ->join('recipients', 'recipients.discussion_id', '=', 'discussions.id')
            ->join('posts', 'posts.discussion_id', '=', 'discussions.id')
            ->leftJoin('flags', 'flags.post_id', '=', 'posts.id')
            ->whereNotNull('flags.id')
            ->whereColumn('posts.discussion_id', 'discussions.id')
            ->orWhereColumn('flags.post_id', 'posts.id');
        });

        if ($actor->hasPermission('user.actorCanViewOnlyFlaggedPosts')) {
            $query->whereExists(function ($query) use ($actor) {
                return $query->selectRaw('1')
                ->from('flags')
                ->whereColumn('flags.post_id', 'posts.id');
            });
        }
    }
}
}