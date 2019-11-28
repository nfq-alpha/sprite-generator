<?php
namespace SpriteGenerator\Command;

use SpriteGenerator\Services\SpriteService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateSpriteCommand extends Command
{
    private $spriteService;

    public function __construct(SpriteService $spriteService)
    {
        parent::__construct();
        $this->spriteService = $spriteService;
    }

    /**
     * configure
     *
     * @access protected
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('nfq:sprite:generate')
            ->setDescription('Generate sprite')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'You can specify the name of one of your sprites. If not set, all the sprites are generated.'
            );
    }

    /**
     * execute
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @access protected
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $name = $input->getArgument('name');
            $output->writeln('<info>Generating your sprites</info>');
            $success = $this->spriteService->generateSprite($name);

            if ($success) {
                $output->writeln('<info>Done</info>');
            }

        } catch (Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }
}
