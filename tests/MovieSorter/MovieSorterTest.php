<?php

use Illuminate\Database\SQLiteConnection;
use StephenFrank\MovieSorter\Movie;
use StephenFrank\StringExploder\Indexer;

require(__DIR__ . '/../../vendor/autoload.php');

class MovieSorterTest extends \PHPUnit_Framework_TestCase
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

    public function testMovie()
    {
        $indexer = $this->newIndexer();

        $indexer->add('videoMedia', 'mp4');
        $indexer->add('srcMedia', 'hd tv');
        $indexer->add('quality', '720p');

        $movie = new Movie('A.movie.2011.HD.TV.720p.Mp4', $indexer);

        $movie->parse();

        $this->assertEquals('MP4', $movie->getVideoMedia());
        $this->assertEquals('HDTV', $movie->getSrcMedia());
        $this->assertEquals('2011', $movie->getYear());
        $this->assertEquals('A movie', $movie->getTitle());

        $this->assertEquals('A movie (2011) [HDTV 720p]', $movie->formatted());
    }
}