<?php

namespace BadMushroom\DigStatsReader;

/**
 * Class Tag
 *
 * The Tag class is a simple data structure that represents a tag in a structured
 * binary file.
 */
class Tag
{
    public int $type;
    public string $name;
    public mixed $value;

    public function __construct(int $type, string $name, mixed $value)
    {
        $this->type = $type;
        $this->name = $name;
        $this->value = $value;
    }
}
