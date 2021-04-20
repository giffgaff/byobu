<?php
namespace FoF\Byobu\Api\Serializer;

use Flarum\Api\Serializer\DiscussionSerializer;

class PrivateDiscussionSerializer extends DiscussionSerializer
{
    protected $type = 'fof-byobu-private-discussions';
}
