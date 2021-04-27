<?php
namespace FoF\Byobu\Api\Overrides;

use Flarum\Discussion\Discussion;
use Flarum\Flags\Api\Controller\DeleteFlagsController;
use FoF\Byobu\Access\ScopeDiscussionVisibility;
use Psr\Http\Message\ServerRequestInterface;

class DeleteFlagsControllerOverride extends DeleteFlagsController
{
    protected function delete(ServerRequestInterface $request)
    {
        Discussion::registerVisibilityScoper(new ScopeDiscussionVisibility(), 'view');
        parent::delete($request);
    }
}
