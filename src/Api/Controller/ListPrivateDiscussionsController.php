<?php
namespace FoF\Byobu\Api\Controller;

use Flarum\Api\Controller\ListDiscussionsController;
use Flarum\Discussion\DiscussionRepository;
use Flarum\Discussion\Search\DiscussionSearcher;
use Flarum\Flags\Flag;
use Flarum\Http\UrlGenerator;
use FoF\Byobu\Api\Serializer\PrivateDiscussionSerializer;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;
use FoF\Byobu\Concerns\ExtensionsDiscovery;

class ListPrivateDiscussionsController extends ListDiscussionsController
{
    use ExtensionsDiscovery;

    public $serializer = PrivateDiscussionSerializer::class;

    protected $discussions;

    public function __construct(DiscussionSearcher $searcher, UrlGenerator $url, DiscussionRepository $discussions)
    {
        parent::__construct($searcher, $url);
        $this->discussions = $discussions;
    }

    /**
     * {@inheritdoc}
     */
    protected function data(ServerRequestInterface $request, Document $document)
    {
        $actor = $request->getAttribute('actor');
        $actor->assertCan('discussion.startPrivateDiscussionWithUsers');

        $query = $this->discussions
                      ->query()
                      ->distinct()
                      ->select('discussions.*')
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
