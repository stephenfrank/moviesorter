<?php


namespace StephenFrank\MovieSorter;


class MovieFile extends MovieDir
{
    public $lastSegmentClean;
    public $fileExt;
    public $acceptedFormats = ['mp4', 'mov', 'avi', 'm4v', 'wmv', 'mpeg', 'mpg', 'mkv'];

    public function __construct($path)
    {
        parent::__construct($path);

        preg_match('/\.(\w{3})$/', $path, $match);

        $this->fileExt = isset($match[1]) ? strtolower($match[1]) : null;

    }

    public function isVideoFile()
    {
        return in_array($this->fileExt, $this->acceptedFormats, true);
    }
}