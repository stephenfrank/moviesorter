<?php


namespace StephenFrank\MovieSorter;


class MovieDir
{
    protected $path;

    protected $indexer;


    public function __construct($path)
    {
        $this->path = $path;

        $parts = explode('/', $path);

        $this->lastSegment = array_pop($parts);
        $this->beginningSegments = implode('/', $parts);
    }

}