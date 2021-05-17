<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Command;

use App\Entity\Race;
use App\Entity\TransactionCategory;
use App\Entity\WitnessCategory;
use App\Repository\RaceRepository;
use App\Repository\TransactionCategoryRepository;
use App\Repository\WitnessCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportWitnessCategories extends Command {
    protected static $defaultName = 'sen:import:witness-categories';

    protected static $defaultDescription = 'Add a short description for your command';

    /**
     * @var WitnessCategoryRepository
     */
    private $repo;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    protected function configure() : void {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('files', InputArgument::IS_ARRAY, 'List of files to import')
            ->addOption('skip', null, InputOption::VALUE_REQUIRED, 'Rows of data to skip', 1)
        ;
    }

    protected function import($file, $skip) {
        $handle = fopen($file, 'r');
        for($i = 0; $i < $skip; $i++) {
            fgetcsv($handle);
        }
        while($row = fgetcsv($handle)) {
            $standard = $row[0];
            $category = $this->repo->findOneBy(['name' => $standard]);
            if( ! $category) {
                $category = new WitnessCategory();
                $category->setName($standard);
                $category->setLabel(mb_convert_case($standard, MB_CASE_TITLE));
                $this->em->persist($category);
                $this->em->flush();
            }
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int {
        $files = $input->getArgument('files');
        $skip = $input->getOption('skip');

        foreach ($files as $file) {
            $this->import($file, $skip);
        }

        return 0;
    }

    /**
     * @param EntityManagerInterface $em
     * @required
     */
    public function setEntityManager(EntityManagerInterface $em) {
        $this->em = $em;
    }

    /**
     * @required
     */
    public function setTransactionCategoryRepository(WitnessCategoryRepository $repo) {
        $this->repo = $repo;
    }
}
