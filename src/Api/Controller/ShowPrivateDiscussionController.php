<?php
namespace FoF\Byobu\Api\Controller;

use Flarum\Api\Controller\AbstractShowController;
use Flarum\Api\Serializer\DiscussionSerializer;
use Flarum\Discussion\Discussion;
use Flarum\Discussion\DiscussionRepository;
use Flarum\Http\SlugManager;
use Flarum\Post\PostRepository;
use Flarum\User\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class ShowPrivateDiscussionController extends AbstractShowController
{
    /**
     * @var \Flarum\Discussion\DiscussionRepository
     */
    protected $discussions;

    /**
     * @var PostRepository
     */
    protected $posts;

    /**
     * @var SlugManager
     */
    protected $slugManager;

    /**
     * {@inheritdoc}
     */
    public $serializer = DiscussionSerializer::class;

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
        'user',
        'lastPostedUser',
        'firstPost',
        'lastPost'
    ];

    /**
     * @param \Flarum\Discussion\DiscussionRepository $discussions
     * @param \Flarum\Post\PostRepository $posts
     * @param \Flarum\Http\SlugManager $slugManager
     */
    public function __construct(DiscussionRepository $discussions, PostRepository $posts, SlugManager $slugManager)
    {
        $this->discussions = $discussions;
        $this->posts = $posts;
        $this->slugManager = $slugManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function data(ServerRequestInterface $request, Document $document)
    {
        $discussionId = Arr::get($request->getQueryParams(), 'id');
        $actor = $request->getAttribute('actor');
        $include = $this->extractInclude($request);

        $discussion = $this->discussions->findOrFail(intval($discussionId));

        if (!$discussion->is_private) {
            $discussion = $this->discussions->findOrFail($discussion->id, $actor);
        }

        if (in_array('posts', $include)) {
            $postRelationships = $this->getPostRelationships($include);

            $this->includePosts($discussion, $request, $postRelationships);
        }

        $discussion->load(array_filter($include, function ($relationship) {
            return ! Str::startsWith($relationship, 'posts');
        }));

        return $discussion;
    }

    /**
     * @param Discussion $discussion
     * @param ServerRequestInterface $request
     * @param array $include
     */
    private function includePosts(Discussion $discussion, ServerRequestInterface $request, array $include)
    {
        $actor = $request->getAttribute('actor');
        $limit = $this->extractLimit($request);
        $offset = $this->getPostsOffset($request, $discussion, $limit);

        $allPosts = $this->loadPostIds($discussion, $actor);
        $loadedPosts = $this->loadPosts($discussion, $actor, $offset, $limit, $include);

        array_splice($allPosts, $offset, $limit, $loadedPosts);

        $discussion->setRelation('posts', $allPosts);
    }

    /**
     * @param Discussion $discussion
     * @param User $actor
     * @return array
     */
    private function loadPostIds(Discussion $discussion, User $actor)
    {
        return $discussion->posts()->whereVisibleTo($actor)->orderBy('created_at')->pluck('id')->all();
    }

    /**
     * @param array $include
     * @return array
     */
    private function getPostRelationships(array $include)
    {
        $prefixLength = strlen($prefix = 'posts.');
        $relationships = [];

        foreach ($include as $relationship) {
            if (substr($relationship, 0, $prefixLength) === $prefix) {
                $relationships[] = substr($relationship, $prefixLength);
            }
        }

        return $relationships;
    }

    /**
     * @param ServerRequestInterface $request
     * @param Discussion$discussion
     * @param int $limit
     * @return int
     */
    private function getPostsOffset(ServerRequestInterface $request, Discussion $discussion, $limit)
    {
        $queryParams = $request->getQueryParams();
        $actor = $request->getAttribute('actor');

        if (($near = Arr::get($queryParams, 'page.near')) > 1) {
            $offset = $this->posts->getIndexForNumber($discussion->id, $near, $actor);
            $offset = max(0, $offset - $limit / 2);
        } else {
            $offset = $this->extractOffset($request);
        }

        return $offset;
    }

    /**
     * @param Discussion $discussion
     * @param User $actor
     * @param int $offset
     * @param int $limit
     * @param array $include
     * @return mixed
     */
    private function loadPosts($discussion, $actor, $offset, $limit, array $include)
    {
        $query = $discussion->posts()->whereVisibleTo($actor);

        $query->orderBy('created_at')->skip($offset)->take($limit)->with($include);

        $posts = $query->get()->all();

        foreach ($posts as $post) {
            $post->discussion = $discussion;
        }

        return $posts;
    }
}
