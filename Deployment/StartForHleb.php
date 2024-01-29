<?php

declare(strict_types=1);

namespace Phphleb\Migration\Deployment;

use Hleb\Main\Console\Commands\Deployer\DeploymentLibInterface;
use Phphleb\Updater\AddAction;
use Phphleb\Updater\RemoveAction;

class StartForHleb implements DeploymentLibInterface
{
    private bool $noInteraction = false;

    private bool $quiet = false;

    public function __construct(private readonly array $config)
    {
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function noInteraction(): void
    {
        $this->noInteraction = true;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function help(): string|false
    {
        return 'Database migrations for the HLEB framework';
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function add(): int
    {
        return (new AddAction($this->config, $this->noInteraction, $this->quiet))->run();
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function remove(): int
    {
        return (new RemoveAction($this->config, $this->noInteraction, $this->quiet))->run();
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function classmap(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function quiet(): void
    {
        $this->quiet = true;
    }
}
