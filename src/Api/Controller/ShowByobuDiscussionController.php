<?php
namespace FoF\Byobu\Api\Controller;

use Flarum\Api\Controller\ShowDiscussionController;
use Flarum\Discussion\Discussion;
use FoF\Byobu\Access\ScopeDiscussionVisibility;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class ShowByobuDiscussionController extends ShowDiscussionController
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
