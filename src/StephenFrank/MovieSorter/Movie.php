<?php


namespace StephenFrank\MovieSorter;


use StephenFrank\StringExploder\AbstractIndexable;
use StephenFrank\StringExploder\Indexer;

class Movie extends AbstractIndexable
{
    /**
     * @var Indexer
     */
    protected $indexer;

    public $original;

    public $beginningSegments;

    public $lastSegment;

    public $title;

    public $wordParts = [];

    public $wordSegments = [];

    protected $indexes = [
        'title',
        'year',
        'quality',
        'srcMedia',
        'videoMedia',
        'audioMedia',
        'authorship'
    ];

    protected $saveIndexes = [
        'quality',
        'srcMedia',
        'videoMedia',
        'audioMedia',
        'authorship'
    ];

    protected $indexOrder = [
        'title', 'year'
    ];

    protected $srcMediaMap = [
        'dvdrip'  => 'DVD',
        'dvdscr'  => 'DVDScr',
        'brrip'   => 'BluRay',
        'bluray'  => 'BluRay',
        'youtube' => 'YouTube',
        'webrip'  => 'WebRip',
        'web-dl' => 'WebRip',
        'web dl' => 'WebRip',
        'hdrip'   => 'HDDVD',
        'hd tv' => 'HDTV'
    ];

    protected $videoMediaMap = [
        '264'  => 'H264',
        'x264'  => 'H264',
        'h264'  => 'H264',
    ];

    public function toArray()
    {
        $arr = [
            'original' => $this->string,
            'title' => $this->getTitle(),
            'formatted' => $this->formatted(),
        ];

        foreach ($this->indexes as $index) {
            $arr[$index] = $this->get($index);
        }

        return $arr;
    }

    public function matchYear($str)
    {
        preg_match('/(19|20)\d{2}/', $str, $match, PREG_OFFSET_CAPTURE);

        if ($match) {
            return $match[0];
        }
    }

    public function getQuality()
    {
        $q = implode(' ', $this->get('quality'));

        preg_match('/\d+/', $q, $numberMatch);

        if ($numberMatch) {
            return $numberMatch[0] . 'p';
        }
    }

    public function getTitle()
    {
        $explicitTitle = $this->get('title');

        if (! empty($explicitTitle)) {
            $title = implode(' ', $this->get('title'));
            return ucfirst($title);
        }

        $title = [];

        foreach ($this->captures as $capture) {
            if ($capture['type'] == 'other') {
                $title[] = $capture['match'];
            } else {
                break;
            }
        }

        $title = implode(' ', $title);

        return ucfirst($title);
    }

    public function getYear()
    {
        $year = $this->get('year');
        $y = end($year);

        return $y ? (int) $y : null;
    }

    public function getSrcMedia()
    {
        $src = $this->get('srcMedia');
        $m = end($src);

        if (isset($this->srcMediaMap[strtolower($m)])) {
            return $this->srcMediaMap[strtolower($m)];
        }

        return $m;
    }

    public function getVideoMedia()
    {
        $videoMedia = $this->get('videoMedia');
        $m = end($videoMedia);

        if (isset($this->videoMediaMap[strtolower($m)])) {
            return $this->videoMediaMap[strtolower($m)];
        }

        return strtoupper($m);
    }

    public function formatted()
    {
        $title = $this->getTitle();
        $year = $this->getYear();
        $srcMedia = $this->getSrcMedia();
        $quality = $this->getQuality();

        $formatted[] = $title;

        if ($year) {
            $formatted[] = '(' . $year . ')';
        }

        if ($srcMedia && $quality) {
            $formatted[] = '[' . $srcMedia . ' ' . $quality . ']';
        } else {
            if ($srcMedia) {
                $formatted[] = '[' . $srcMedia . ']';
            }
            if ($quality) {
                $formatted[] = '[' . $quality . ']';
            }
        }

        return implode(' ', $formatted);
    }

}