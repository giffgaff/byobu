<?php
namespace FoF\Byobu\Api\Overrides;

use Flarum\Api\Controller\DeleteDiscussionController;
use Flarum\Discussion\Discussion;
use Psr\Http\Message\ServerRequestInterface;
use FoF\Byobu\Access\ScopeDiscussionVisibility;


class DeleteDiscussionControllerOverride extends DeleteDiscussionController
{
    protected function delete(ServerRequestInterface $request)
    {
        Discussion::registerVisibilityScoper(new ScopeDiscussionVisibility(), 'view');
        parent::delete($request);
    }
}
