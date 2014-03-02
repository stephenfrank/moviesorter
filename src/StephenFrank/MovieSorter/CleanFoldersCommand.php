<?php


namespace StephenFrank\MovieSorter;


use Illuminate\Database\SQLiteConnection;
use PDO;
use StephenFrank\StringExploder\AbstractIndexingCommand;
use StephenFrank\StringExploder\Indexer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CleanFoldersCommand extends AbstractIndexingCommand
{
    protected $indexOptions = [
        't' => 'Title (or part of the title)',
        'y' => 'Year (eg. 2001)',
        'q' => 'Quality indicator (eg. 1080p)',
        'm' => 'Source Media (eg. BRRip)',
        'v' => 'Video Format (eg. XVid)',
        'a' => 'Audio Format (eg. AC3)',
        'n' => 'Authorship name (eg. g3noc1d3)',
    ];

    protected $indexOptionsMap = [
        't' => 'title',
        'y' => 'year',
        'q' => 'quality',
        'm' => 'srcMedia',
        'v' => 'videoMedia',
        'a' => 'audioMedia',
        'n' => 'authorship',
    ];

    protected function getIndexer()
    {
        $pdo = new PDO('sqlite:' . DB_NAME , null, null, [
            PDO::ATTR_CASE => PDO::CASE_NATURAL,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
        ]);

        $connection = new SQLiteConnection($pdo, DB_NAME, '', ['driver' => 'sqlite']);

        $indexer = new Indexer($connection);
        $indexer->rebuild();

        return $indexer;

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');
        $isDry = $input->getOption('dry');
        $isRedo = $input->getOption('redo');

        $glob = glob(rtrim($path, '/') . '/*');

        $indexer = $this->getIndexer();

        foreach ($glob as $f) {
            if (!is_dir($f)) {
                continue;
            }

            $movieDir = new MovieDir($f, $indexer);

            $infoFile = $f . '/.info.json';

            if (file_exists($infoFile)) {
                if (!$isRedo) {
                    continue;
                }

                $infoJson = file_get_contents($infoFile);
                $info = json_decode($infoJson, true);

                $movie = new Movie($info['original'], $indexer);
                $movie->parse();

                $this->processInput($movie, $indexer, $output);
            } else {
                $movie = new Movie($movieDir->lastSegment, $indexer);
                $movie->parse();

                $this->processInput($movie, $indexer, $output);
            }

            // Make directory
            $originalName = $f;

            $formatted = $movie->formatted();

            $newName = $movieDir->beginningSegments . '/' . $formatted;

            if (!$isDry) {
                $output->writeln('Rename: ' . $originalName . ' -> ' . $newName . '?');
                $choice = $this->readline("Confirm Y/n? ");

                if ($choice == '' || $choice == 'Y' || $choice == 'y') {
                    rename($originalName, $newName);
                    file_put_contents($newName . '/.info.json', json_encode($movie->toArray()));
                }
            } else {
                $output->writeln($originalName . ' -> ' . $newName);
            }
        }
    }

    protected function configure()
    {
        $this
            ->setName('moviesorter:cleanfolders')
            ->setDescription('Sort some movies')
            ->addArgument(
                'path',
                InputArgument::OPTIONAL,
                'Movie folder path'
            )
            ->addOption(
                '--dry',
                null,
                InputOption::VALUE_NONE,
                'Run without applying changes'
            )
            ->addOption(
                '--redo',
                null,
                InputOption::VALUE_NONE,
                'Redo already processed folders'
            );
    }

}