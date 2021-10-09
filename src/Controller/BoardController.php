<?php

declare(strict_types=1);

namespace KanbanBoard\Controller;

use KanbanBoard\Exception\Git\UnsupportedTypeException;
use KanbanBoard\Factory\GitFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BoardController extends AbstractController
{
    private GitFactory $gitFactory;

    public function __construct(GitFactory $gitFactory)
    {
        $this->gitFactory = $gitFactory;
    }

    /**
     * @throws UnsupportedTypeException
     */
    public function boardAction()
    {
        $repositories = explode(',', $_ENV['GH_REPOSITORIES']);

        $git = $this->gitFactory->createDefault();

        $milestones = [];

        foreach ($repositories as $repository) {
            $milestones = array_merge($milestones, $git->getMilestones($repository));
        }
    }
}
