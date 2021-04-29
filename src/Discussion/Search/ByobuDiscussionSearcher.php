<?php
namespace FoF\Byobu\Discussion\Search;

use Flarum\Discussion\DiscussionRepository;
use Flarum\Discussion\Event\Searching;
use Flarum\Discussion\Search\DiscussionSearch;
use Flarum\Discussion\Search\DiscussionSearcher;
use Flarum\Search\GambitManager;
use Flarum\Search\SearchCriteria;
use Flarum\Search\SearchResults;
use Flarum\User\UserRepository;
use Illuminate\Contracts\Events\Dispatcher;
use FoF\Byobu\Database\RecipientsConstraint;


class ByobuDiscussionSearcher extends DiscussionSearcher
{
    use RecipientsConstraint;

    protected $users;

    /**
     * @param GambitManager $gambits
     * @param DiscussionRepository $discussions
     * @param Dispatcher $events
     */
    public function __construct(GambitManager $gambits, DiscussionRepository $discussions, Dispatcher $events, UserRepository $users)
    {
        DiscussionSearcher::__construct($gambits, $discussions, $events);
        $this->users = $users;
    }

    private function getPrivateDiscussions(SearchCriteria $criteria, $limit = null, $offset = 0)
    {
        $actor = $criteria->actor;

        $filterByUser = null;
        foreach(explode(' ', $criteria->query) as $bit) {
            preg_match('/^byobu:(.+)$/i', $bit, $queryPattern);
            if ($queryPattern) {
                list($pattern, $username) = $queryPattern;
                $filterByUser = $this->users->findOrFailByUsername($username);
            }
        }

        $query = $this->discussions
            ->query()
            ->distinct()
            ->select('discussions.*')
            ->join('recipients', 'recipients.discussion_id', '=', 'discussions.id')
            ->whereNull('recipients.removed_at')
            ->where('recipients.user_id', $actor->id);



        if ($filterByUser) {
            $filterByUserQuery = $this->discussions->query()
                ->select('discussions.id')
                ->join('recipients', 'recipients.discussion_id', '=', 'discussions.id')
                ->whereNull('recipients.removed_at')
                ->where('recipients.user_id', $filterByUser->id);

            $query->whereIn('discussions.id', $filterByUserQuery);
        }

        if ($this->flagsInstalled()
            && $actor->hasPermission('user.viewPrivateDiscussionsWhenFlagged')
            && $actor->hasPermission('discussion.viewFlags')) {
            $query->orWhereIn('recipients.discussion_id', function ($query) {
                $query->select('posts.discussion_id')
                    ->from('posts')
                    ->join('flags', 'flags.post_id', 'posts.id');
            });
        }

        $search = new DiscussionSearch($query->getQuery(), $actor);
        $this->applyOffset($search, $offset);
        $this->applyLimit($search, $limit + 1);
        $this->applySort($search, $criteria->sort);

        $discussions = $query->get();

        $areMoreResults = $limit > 0 && $discussions->count() > $limit;

        if ($areMoreResults) {
            $discussions->pop();
        }

        return new SearchResults($discussions, $areMoreResults);
    }

    public function search(SearchCriteria $criteria, $limit = null, $offset = 0)
    {
        $isPrivate = in_array('is:private', explode(' ', $criteria->query));

        if ($isPrivate) {
            return $this->getPrivateDiscussions($criteria, $limit, $offset);
        }

        return parent::search($criteria, $limit, $offset);
    }
}
