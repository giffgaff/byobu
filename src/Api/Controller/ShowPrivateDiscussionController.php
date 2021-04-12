<?php
namespace FoF\Byobu\Api\Controller;

use Flarum\Api\Controller\AbstractShowController;
use Flarum\Api\Serializer\DiscussionSerializer;
use Flarum\Discussion\Discussion;
use Flarum\Discussion\DiscussionRepository;
use Flarum\Flags\Flag;
use Flarum\Http\SlugManager;
use Flarum\Post\PostRepository;
use Flarum\User\User;
use FoF\Byobu\Api\Serializer\PrivateDiscussionSerializer;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;
use FoF\Byobu\Concerns\ExtensionsDiscovery;

class ShowPrivateDiscussionController extends AbstractShowController
{
    use ExtensionsDiscovery;

    /**
     * @var \Flarum\Discussion\DiscussionRepository
     */
    protected $discussions;

    /**
     * @var PostRepository
     */
    protected $posts;

    /**
     * {@inheritdoc}
     */
    public $serializer = PrivateDiscussionSerializer::class;

    /**
     * {@inheritdoc}
     */
    public $include = [
        'posts',
        'posts.discussion',
        'posts.user',
        'posts.user.groups',
        'posts.editedUser',
        'posts.hiddenUser'
    ];

    /**
     * {@inheritdoc}
     */
    public $optionalInclude = [
        'user'
    ];

    /**
     * @param \Flarum\Discussion\DiscussionRepository $discussions
     * @param \Flarum\Post\PostRepository $posts
     */
    public function __construct(DiscussionRepository $discussions, PostRepository $posts)
    {
        $this->discussions = $discussions;
        $this->posts = $posts;
    }

    /**
     * {@inheritdoc}
     */
    protected function data(ServerRequestInterface $request, Document $document)
    {
        $actor = $request->getAttribute('actor');
        $actor->assertCan('discussion.startPrivateDiscussionWithUsers');

        $discussionId = Arr::get($request->getQueryParams(), 'id');
        $include = $this->extractInclude($request);

        $query = $this->discussions
                      ->query()
                      ->distinct()
                      ->select('discussions.*')
                      ->join('recipients', 'recipients.discussion_id', '=', 'discussions.id')
                      ->whereNull('recipients.removed_at')
                      ->where('recipients.user_id', $actor->id)
                      ->where('discussions.id', $discussionId);

        if ($this->canViewFlaggedPrivateDiscussions($actor)) {
            $query->orWhereIn('discussions.id', function ($query) {
                $query->select('posts.discussion_id')
                    ->from('posts')
                    ->join('flags', 'flags.post_id', 'posts.id');
            });
        }

        $query->limit(1);
        $discussion = $query->firstOrFail();

        $discussion->load($include);

        return $discussion;
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
