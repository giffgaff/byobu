<?php
namespace FoF\Byobu\Api\Overrides;

use Flarum\Api\Controller\DeletePostController;
use Flarum\Discussion\Discussion;
use Psr\Http\Message\ServerRequestInterface;
use FoF\Byobu\Access\ScopeDiscussionVisibility;

class DeletePostControllerOverride extends DeletePostController
{
    protected function delete(ServerRequestInterface $request)
    {
        Discussion::registerVisibilityScoper(new ScopeDiscussionVisibility(), 'view');
        parent::delete($request);
    }
}
