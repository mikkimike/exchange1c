<?php

namespace Mikkimike\Exchange1C\PayloadTypes;

class RelationProducts implements PayloadTypeInterface
{
    public $relations;

    public function __construct(array $relations)
    {
        $this->relations = $relations;
    }
}
