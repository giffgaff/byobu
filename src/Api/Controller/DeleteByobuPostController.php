<?php
namespace FoF\Byobu\Api\Controller;

use Flarum\Api\Controller\DeletePostController;
use Flarum\Discussion\Discussion;
use FoF\Byobu\Access\ScopeDiscussionVisibility;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class DeleteByobuPostController extends DeletePostController
{
    /**
     * {@inheritdoc}
     */
    protected function data(ServerRequestInterface $request, Document $document)
    {
        Discussion::registerVisibilityScoper(new ScopeDiscussionVisibility(), 'view');
        return parent::data($request, $document);
    }
}
