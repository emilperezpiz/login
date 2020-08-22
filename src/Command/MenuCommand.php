<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Document\Menu;

class MenuCommand extends Command
{
    protected static $defaultName = 'app:menu';

    private $container;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct();
        $this->container = $container;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /*$io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            // ...
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');*/

        $dm = $this->container->get('doctrine_mongodb')->getManager();

        $restaurant = $dm
            ->getRepository('App:BussinesClasification')
            ->findOneBy(array(
                'identificador' => '200'
            ));

        $business = $dm
            ->getRepository('App:Business')
            ->findBy(array(
                'isActive' => true,
                'bussinesClasification' => $restaurant
            ));

        $scheduled = new \DateTime("00:00:00");

        foreach ($business as $item) {

            $issetBusiness = $dm
                ->getRepository('App:Menu')
                ->findBy(array(
                    'bussines' => $item,
                    'scheduled' => $scheduled
                ));

            if (!$issetBusiness) {

                $menu = new Menu();
                $menu->setScheduled($scheduled);
                $menu->setBussines($item);

                $products = $dm
                    ->getRepository('App:Product')
                    ->findBy(array(
                        'bussines' => $item,
                        'isActive' => true
                    ));

                foreach ($products as $product) {

                    $menu->setProduct($product);
                }

                $dm->persist($menu);
            }
        }

        $dm->flush();
    }
}
