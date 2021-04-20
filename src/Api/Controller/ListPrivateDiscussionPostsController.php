<?php
namespace FoF\Byobu\Api\Controller;

use Flarum\Api\Controller\AbstractListController;
use Flarum\Event\ConfigurePostsQuery;
use Flarum\Flags\Flag;
use Flarum\Post\PostRepository;
use FoF\Byobu\Api\Serializer\PrivatePostSerializer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;
use FoF\Byobu\Concerns\ExtensionsDiscovery;
use Tobscure\JsonApi\Exception\InvalidParameterException;

class ListPrivateDiscussionPostsController extends AbstractListController
{
    use ExtensionsDiscovery;

    /**
     * {@inheritdoc}
     */
    public $serializer = PrivatePostSerializer::class;

    /**
     * {@inheritdoc}
     */
    public $include = [
        'user',
        'user.groups',
        'editedUser',
        'hiddenUser',
        'discussion'
    ];

    /**
     * {@inheritdoc}
     */
    public $sortFields = ['createdAt'];

    /**
     * @var \Flarum\Post\PostRepository
     */
    protected $posts;

    /**
     * @param \Flarum\Post\PostRepository $posts
     */
    public function __construct(PostRepository $posts)
    {
        $this->posts = $posts;
    }

    /**
     * {@inheritdoc}
     */
    protected function data(ServerRequestInterface $request, Document $document)
    {
        $actor = $request->getAttribute('actor');
        $filter = $this->extractFilter($request);
        $include = $this->extractInclude($request);

        $actor->assertCan('discussion.startPrivateDiscussionWithUsers');
        $postIds = Arr::get($filter, 'id');

        return $this->getPrivatePosts($request, $postIds)->load($include);

    }

    /**
     * {@inheritdoc}
     */
    protected function extractOffset(ServerRequestInterface $request)
    {
        $actor = $request->getAttribute('actor');
        $queryParams = $request->getQueryParams();
        $sort = $this->extractSort($request);
        $limit = $this->extractLimit($request);
        $filter = $this->extractFilter($request);

        if (($near = Arr::get($queryParams, 'page.near')) > 1) {
            if (count($filter) > 1 || ! isset($filter['discussion']) || $sort) {
                throw new InvalidParameterException(
                    'You can only use page[near] with filter[discussion] and the default sort order'
                );
            }

            $offset = $this->posts->getIndexForNumber($filter['discussion'], $near, $actor);

            return max(0, $offset - $limit / 2);
        }

        return parent::extractOffset($request);
    }

    /**
     * @param ServerRequestInterface $request
     * @param array $postIds
     * @return array
     * @throws InvalidParameterException
     */
    private function getPrivatePosts(ServerRequestInterface $request, array $postIds = null)
    {
        $actor = $request->getAttribute('actor');
        $filter = $this->extractFilter($request);
        $sort = $this->extractSort($request);
        $limit = $this->extractLimit($request);
        $offset = $this->extractOffset($request);

        $discussionId = Arr::get($request->getQueryParams(), 'discussionId');

        $query = $this->posts->query()
            ->select('posts.*')
            ->join('discussions', 'posts.discussion_id', '=', 'discussions.id')
            ->join('recipients', 'recipients.discussion_id', '=', 'discussions.id')
            ->whereNull('recipients.removed_at')
            ->where('recipients.user_id', $actor->id)
            ->where('discussions.id', $discussionId);

        if ($postIds && !empty($postIds)) {
            $query->whereIn('posts.id', $postIds);
        }

        if ($this->canViewFlaggedPrivateDiscussions($actor)) {
            $query->orWhereIn('recipients.discussion_id', function ($query) {
                $query->select('posts.discussion_id')
                    ->from('posts')
                    ->join('flags', 'flags.post_id', 'posts.id');
            });
        }

        $this->applyFilters($query, $filter);

        $query->skip($offset)->take($limit);

        foreach ((array) $sort as $field => $order) {
            $query->orderBy(Str::snake($field), $order);
        }

        return $query->get();
    }

    /**
     * @param Builder $query
     * @param array $filter
     */
    private function applyFilters(Builder $query, array $filter)
    {
        if ($discussionId = Arr::get($filter, 'discussionId')) {
            $query->where('posts.discussion_id', $discussionId);
        }

        if ($number = Arr::get($filter, 'number')) {
            $query->where('posts.number', $number);
        }

        if ($userId = Arr::get($filter, 'user')) {
            $query->where('posts.user_id', $userId);
        }

        if ($type = Arr::get($filter, 'type')) {
            $query->where('posts.type', $type);
        }

        event(new ConfigurePostsQuery($query, $filter));
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
