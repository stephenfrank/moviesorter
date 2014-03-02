<?php

namespace StringExploder;

use Illuminate\Database\SQLiteConnection;
use PDO;
use PHPUnit_Framework_TestCase;
use StephenFrank\StringExploder\AbstractIndexable;
use StephenFrank\StringExploder\AbstractIndexingCommand;
use StephenFrank\StringExploder\Indexer;
use Symfony\Component\Console\Output\Output;

require(__DIR__ . '/../../vendor/autoload.php');

class StringExploderTest extends PHPUnit_Framework_TestCase
{
    private function newIndexer()
    {
        $pdo = new PDO('sqlite:' . __DIR__.'/index_db_test.db' , null, null, [
            PDO::ATTR_CASE => PDO::CASE_NATURAL,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
        ]);

        $connection = new SQLiteConnection($pdo, 'TEST_DB', '', ['driver' => 'sqlite']);

        $indexer = new Indexer($connection);
        $indexer->rebuild();

        return $indexer;
    }

    public function testIndexer()
    {
        $indexer = $this->newIndexer();

        $indexer->add('foo', 'bar');
        $indexer->add('foo', 'bar');

        $count = $indexer->getOccurrence('foo', 'bar');

        $this->assertEquals(2, $count);
    }

    public function testSaveIndexable()
    {
        $indexer = $this->newIndexer();
        $indexable = new TestIndexable('', $indexer);

        $indexable->add('videoMedia', 'mp4');
        $indexable->add('srcMedia', 'hd tv');
        $indexable->add('quality', '720p');

        $indexer->saveIndexable($indexable);

    }

    public function testIndexable()
    {
        $indexer = $this->newIndexer();

        $indexer->add('videoMedia', 'mp4');
        $indexer->add('srcMedia', 'hd tv');
        $indexer->add('quality', '720p');

        $indexable = new TestIndexable('A.movie.2011.HD.TV.720p.Mp4', $indexer);

        $indexable->parse();

        $this->assertEquals(['mp4'], $indexable->get('videoMedia'));
        $this->assertEquals(['hd tv'], $indexable->get('srcMedia'));
        $this->assertEquals(['720p'], $indexable->get('quality'));
    }

    public function testCommand()
    {
        $command = new TestCommand('testcmd');

        $output = new TestOutput();

        $indexer = $this->newIndexer();
        $indexable = new TestIndexable('test', $indexer);

        $indexable->parse();

        $command->processInput($indexable, $indexer, $output);

        $this->assertEquals(['test'], $indexable->get('srcMedia'));
    }

}

class TestIndexable extends AbstractIndexable
{
    protected $indexes = ['srcMedia', 'videoMedia', 'quality'];
    protected $saveIndexes = ['srcMedia', 'videoMedia', 'quality'];
}

class TestCommand extends AbstractIndexingCommand
{
    private $name = 'testcmd';

    protected $indexOptions = [
        'a' => 'A test'
    ];
    protected $indexOptionsMap = [
        'a' => 'srcMedia'
    ];

    protected function readline($str)
    {
        return 'a';
    }
};

class TestOutput extends Output
{
    public $output = '';

    public function clear()
    {
        $this->output = '';
    }

    protected function doWrite($message, $newline)
    {
        $this->output .= $message.($newline ? "\n" : '');
    }
}
