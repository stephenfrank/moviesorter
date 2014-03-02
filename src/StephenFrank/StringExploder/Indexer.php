<?php


namespace StephenFrank\StringExploder;

use Illuminate\Database\ConnectionInterface;

class Indexer
{
    /**
     * @var ConnectionInterface
     */
    protected $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function getTypes()
    {
        $rows = $this->connection->table('phrases')->distinct()->get(['type']);

        return array_pluck($rows, 'type');
    }

    public function getPhrasesByType($type)
    {
        $rows = $this->connection->table('phrases')->distinct()->where('type', $type)->get(['phrase']);

        return array_pluck($rows, 'phrase');
    }

    public function rebuild()
    {
        $builder = $this->connection->getSchemaBuilder();

        $builder->dropIfExists('phrases');

        $builder->create('phrases', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('type')->index();
                $table->string('phrase')->index();
            });
    }

    public function inIndex($word, $minOccurrences = 1, $searchIndex = null)
    {
        $query = $this->connection->table('phrases')->where('phrase', strtolower($word));

        if ($searchIndex) {
            $query = $query->where('type', $searchIndex);
        }

        $row = $query->first();

        if ($row) {
            $occurrences = $this->getOccurrence($row['type'], $word);

            if ($occurrences >= $minOccurrences) {
                return $row['type'];
            }
        }

        return false;
    }

    public function getOccurrence($index, $word)
    {
        return $this->connection->table('phrases')->where('type', $index)->where('phrase', $word)->count();
    }

    public function add($index, $word)
    {
        if (empty($word)) {
            return false;
        }

        $word = strtolower($word);

        $this->connection->table('phrases')->insert([
                'type' => $index,
                'phrase' => $word
            ]);
    }

    public function saveIndexable(AbstractIndexable $indexable)
    {
        $indexes = $indexable->getIndexesToSave();

        foreach ($indexes as $index) {
            $indexed = $indexable->get($index);

            foreach ($indexed as $i) {
                $this->add($index, $i);
            }
        }
    }

}