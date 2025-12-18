<?php

namespace App\Command;

use OpenApi\Generator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:openapi:generate',
    description: 'Generate OpenAPI spec into public/api/openapi.json'
)]
final class OpenApiGenerateCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectDir = \dirname(__DIR__, 2);

        $openapi = Generator::scan([
            $projectDir . '/src',
        ]);

        $targetDir = $projectDir . '/public/api';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        file_put_contents($targetDir . '/openapi.json', $openapi->toJson());

        $output->writeln('<info>Generated:</info> public/api/openapi.json');

        return Command::SUCCESS;
    }
}
