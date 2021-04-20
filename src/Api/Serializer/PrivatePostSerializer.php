<?php
namespace FoF\Byobu\Api\Serializer;

use Flarum\Api\Serializer\PostSerializer;

class PrivatePostSerializer extends PostSerializer
{
    protected $type = 'fof-byobu-private-posts';
}
