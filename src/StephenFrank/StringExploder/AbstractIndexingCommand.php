<?php


namespace StephenFrank\StringExploder;


use Symfony\Component\Console\Command\Command;

class AbstractIndexingCommand extends Command
{
    protected $indexOptions = [];

    protected $indexOptionsMap = [];

    public function __construct($name = null)
    {
        parent::__construct($name);

        if (empty($this->indexOptions)) {
            throw new \Exception("AbstractIndexingCommand requires the indexOptions property");
        }

        if (empty($this->indexOptionsMap)) {
            throw new \Exception("AbstractIndexingCommand requires the indexOptions property");
        }

    }

    public function processInput(AbstractIndexable $indexable, Indexer $indexer, $output)
    {
        $captures = [];

        foreach ($indexable->getCaptures() as $capture) {
            if ($capture['type'] == 'other') {
                $captures[] = $capture;
            }
        }

        $colorized = $this->colorizeTitle($indexable->getString(), $captures);

        foreach ($captures as $capture) {
            $wordCompound[] = $capture['match'];
            $line = $colorized;

            foreach ($wordCompound as $word) {
                $line = str_replace("<comment>" . $word . "</comment>", "<info>$word</info>", $line);
            }

            $output->writeln($line);

            $this->outputIndexOptions($output);

            $indexCode = $this->readline("Choose index: ");
            $chosenIndex = isset($this->indexOptionsMap[$indexCode]) ? $this->indexOptionsMap[$indexCode] : null;

            if ($indexCode == 's' || !$chosenIndex) {
                $wordCompound = [];
                continue;
            }

            if ($indexCode === ">") {
                continue;
            }

            $indexable->add($chosenIndex, implode(' ', $wordCompound));

            $wordCompound = [];
        }

        $indexer->saveIndexable($indexable);

    }

    protected function colorizeTitle($colorized, $captures)
    {
        $offset = 0;

        foreach ($captures as $capture) {
            $firstSegment = substr($colorized, 0, $capture['start'] + $offset);
            $middleSegment = substr($colorized, $capture['start'] + $offset, $capture['length']);
            $endSegment = substr($colorized, $capture['end'] + $offset);

            $colorized = $firstSegment .
                '<comment>' . $middleSegment . '</comment>' .
                $endSegment;

            $offset += strlen('<comment></comment>');
        }

        return $colorized;
    }

    public function outputIndexOptions($output)
    {
        $output->writeln("What is this?");
        $output->writeln("   s: Skip");
        $output->writeln("   >: Join with next word");

        foreach ($this->indexOptions as $key => $description) {
            $output->writeln("   $key: $description");
        }
    }

    protected function readline($str)
    {
        return readline($str);
    }

} 