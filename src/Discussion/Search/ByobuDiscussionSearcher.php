<?php
namespace FoF\Byobu\Discussion\Search;

use Flarum\Discussion\DiscussionRepository;
use Flarum\Discussion\Event\Searching;
use Flarum\Discussion\Search\DiscussionSearch;
use Flarum\Discussion\Search\DiscussionSearcher;
use Flarum\Search\GambitManager;
use Flarum\Search\SearchCriteria;
use Flarum\Search\SearchResults;
use Illuminate\Contracts\Events\Dispatcher;
use FoF\Byobu\Database\RecipientsConstraint;


class ByobuDiscussionSearcher extends DiscussionSearcher
{
    use RecipientsConstraint;

    private function getPrivateDiscussions(SearchCriteria $criteria, $limit = null, $offset = 0)
    {
        $actor = $criteria->actor;
        $query = $this->discussions->query()->select('discussions.*');

        $this->constraint($query, $actor, false);
        $query->orderBy('created_at', 'desc');

        $search = new DiscussionSearch($query->getQuery(), $actor);
        $this->applyOffset($search, $offset);
        $this->applyLimit($search, $limit + 1);

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
