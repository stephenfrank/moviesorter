<?php


namespace StephenFrank\MovieSorter;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FilesToFoldersCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('moviesorter:filestofolders')
            ->setDescription('Sort some movies')
            ->addArgument(
                'path',
                InputArgument::OPTIONAL,
                'Movie folder path'
            )
            ->addOption(
                'yell',
                null,
                InputOption::VALUE_NONE,
                'If set, the task will yell in uppercase letters'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');

        $glob = glob(rtrim($path, '/') . '/*');

        foreach ($glob as $f) {
            if (is_file($f)) {
                $movieFile = new MovieFile($f, null);

                if (! $movieFile->isVideoFile()) {
                    continue;
                }

                // Make directory
                $originalName = $movieFile->lastSegment;
                $tmpName = $movieFile->beginningSegments . '/' . md5($movieFile->lastSegment);
                $newName = $f . '/' . $movieFile->lastSegment;

                $output->writeln($originalName . ' -> ' . $tmpName . ' -> ' . $newName);

                rename($f, $tmpName);

                mkdir($f);

                rename($tmpName, $f . '/' . $movieFile->lastSegment);

            }
        }
    }

} 