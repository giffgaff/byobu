<?php
namespace FoF\Byobu\Api\Controller;

use Flarum\Api\Controller\ListDiscussionsController;
use Flarum\Api\Controller\ListPostsController;
use Flarum\Api\Serializer\PostSerializer;
use Flarum\Discussion\DiscussionRepository;
use Flarum\Discussion\Search\DiscussionSearcher;
use Flarum\Flags\Flag;
use Flarum\Http\UrlGenerator;
use Flarum\Post\PostRepository;
use FoF\Byobu\Api\Serializer\PrivateDiscussionSerializer;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;
use FoF\Byobu\Concerns\ExtensionsDiscovery;

class ListPrivateDiscussionPostsController extends ListPostsController
{
    use ExtensionsDiscovery;

    public $serializer = PostSerializer::class;

    /**
     * {@inheritdoc}
     */
    protected function data(ServerRequestInterface $request, Document $document)
    {
        $actor = $request->getAttribute('actor');
        $actor->assertCan('discussion.startPrivateDiscussionWithUsers');

        $query = $this->posts
            ->query()
            ->distinct()
            ->select('posts.*')
            ->join('discussions', 'posts.discussion_id', '=', 'discussions.id')
            ->join('recipients', 'recipients.discussion_id', '=', 'discussions.id')
            ->whereNull('recipients.removed_at')
            ->where('recipients.user_id', $actor->id);


        if ($this->canViewFlaggedPrivateDiscussions($actor)) {
            $query->orWhereIn('recipients.discussion_id', function ($query) {
                $query->select('posts.discussion_id')
                    ->from('posts')
                    ->join('flags', 'flags.post_id', 'posts.id');
            });
        }

        return $query->get();
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
