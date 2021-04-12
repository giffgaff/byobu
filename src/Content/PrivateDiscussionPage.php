<?php

/*
 * This file is part of Flarum.
 *
 * For detailed copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */

namespace FoF\Byobu\Content;

use Flarum\Forum\Content\Discussion;
use Flarum\Http\Exception\RouteNotFoundException;
use Flarum\User\User;
use FoF\Byobu\Api\Controller\ShowPrivateDiscussionController;

class PrivateDiscussionPage extends Discussion
{
    /**
     * @inheritDoc
     */
    protected function getApiDocument(User $actor, array $params)
    {
        $params['bySlug'] = true;
        $response = $this->api->send(ShowPrivateDiscussionController::class, $actor, $params);
        $statusCode = $response->getStatusCode();

        if ($statusCode === 404) {
            throw new RouteNotFoundException;
        }

        return json_decode($response->getBody());
    }
}
