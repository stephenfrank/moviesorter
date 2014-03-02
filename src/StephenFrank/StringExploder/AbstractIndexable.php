<?php


namespace StephenFrank\StringExploder;


abstract class AbstractIndexable
{
    protected $string;

    protected $indexer;

    protected $indexed;

    protected $indexes = [];

    protected $saveIndexes = [];

    protected $indexOrder = [];

    protected $captures = [];

    public function __construct($string, Indexer $indexer = null)
    {
        $this->string = $string;
        $this->indexer = $indexer;

        foreach ($this->indexes as $index) {
            $this->indexed[$index] = [];
        }
    }

    /**
     * @return mixed
     */
    public function getString()
    {
        return $this->string;
    }

    public function getCaptures()
    {
        return $this->captures;
    }

    public function parse()
    {
        $original = $this->string;
        $original = preg_replace('/-(\w+)$/', ' $1', $original);

        foreach ($this->indexes as $type) {
            $matchMethod = 'match' . ucfirst($type);

            if (method_exists($this, $matchMethod)) {
                if ($match = $this->$matchMethod($original)) {
                    $this->pushMatch($match[0], $match[1], $type);
                }
            } else {
                if (in_array($type, $this->saveIndexes)) {
                    $phrases = $this->indexer->getPhrasesByType($type);

                    foreach ($phrases as $phrase) {
                        $parts = explode(' ', $phrase);
                        $pattern = '/' . implode('[\.\_\s-]', $parts) . '/i';

                        preg_match($pattern, $original, $match, PREG_OFFSET_CAPTURE);

                        if ($match) {
                            $this->pushMatch($match[0][0], $match[0][1], $type);
                        }
                    }
                }
            }
        }

        foreach ($this->captures as $capture) {
            for ($i = $capture['start']; $i < $capture['end']; $i++) {
                $original[$i] = ' ';
            }
        }

        preg_match_all('/[a-zA-Z0-9-]+/', $original, $leftOver, PREG_OFFSET_CAPTURE);

        if (isset($leftOver[0])) {
            foreach ($leftOver[0] as $match) {
                $this->pushMatch($match[0], $match[1], 'other');
            }
        }

        uasort($this->captures,
            function ($a, $b) {
                return $a['start'] < $b['start'] ? -1 : 1;
            }
        );

        foreach ($this->captures as $capture) {
            $type = $capture['type'];

            if ($type == 'other') {
                continue;
            }

            $cleanPhrase = $this->cleanPhrase($capture['match']);

            array_push($this->indexed[$type], $cleanPhrase);
        }
    }

    public function pushMatch($str, $offset, $type)
    {
        $this->captures[] = [
            'start'  => $offset,
            'end'    => $offset + strlen($str),
            'match'  => $str,
            'length' => strlen($str),
            'type'   => $type
        ];
    }

    public function cleanPhrase($word)
    {
        $word = strtolower($word);

        preg_match_all('/[\w-]+/', $word, $matches);

        if (!isset($matches[0])) {
            return null;
        }

        return implode(' ', $matches[0]);
    }

    public function load($indexed)
    {
        foreach ($this->indexes as $index) {
            if (isset($indexed[$index])) {
                $this->indexed[$index] = $indexed[$index];
            }
        }
    }

    public function add($name, $value)
    {
        $this->indexed[$name][] = $value;
    }

    public function getIndexed()
    {
        return $this->indexed;
    }

    public function getIndexesToSave()
    {
        return $this->saveIndexes;
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    public function get($name)
    {
        if (!in_array($name, $this->indexes)) {
            throw new \InvalidArgumentException("$name is not a valid index");
        }
        return isset($this->indexed[$name]) ? $this->indexed[$name] : null;
    }

    public function set($name, $value)
    {
        if (!in_array($name, $this->indexes)) {
            throw new \InvalidArgumentException("$name is not a valid index");
        }

        $this->indexed[$name] = $value;
    }

}