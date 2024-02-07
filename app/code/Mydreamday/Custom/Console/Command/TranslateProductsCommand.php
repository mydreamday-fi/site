<?php
namespace Mydreamday\Custom\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;

class TranslateProductsCommand extends Command
{
    protected $appState;

    public function __construct(
        State $appState
    ) {
        $this->appState = $appState;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('mydreamday:translate-products-se')
            ->setDescription('Translate products to Swedish')
            ->setDefinition([
                new InputArgument('product_ids', InputArgument::IS_ARRAY, 'Product IDs')
            ]);

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Run the reindex command first to make sure all rules are applied correctly
		$reindexCommand = 'bin/magento mirasvit:assistant:reindex';
		$output->writeln("Running reindex command: $reindexCommand");
		$reindexCommandOutput = shell_exec($reindexCommand);
		$output->writeln($reindexCommandOutput);
		
		$productIds = $input->getArgument('product_ids');
        $ruleIds = [7, 2, 4, 5, 6]; // For swedish we are applying these rules to translate a product
		
        foreach ($productIds as $productId) {
            foreach ($ruleIds as $ruleId) {
                $command = 'bin/magento mirasvit:assistant:apply-rule --id ' . $ruleId . ' --entity-id ' . $productId;
                $output->writeln("Running command: $command");
                $commandOutput = shell_exec($command);
                $output->writeln($commandOutput);
            }
        }

        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}
