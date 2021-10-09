<?php

declare(strict_types=1);

namespace KanbanBoard\Controller;

use KanbanBoard\Exception\Git\UnsupportedTypeException;
use KanbanBoard\Factory\GitFactory;
use Mustache_Engine;
use Mustache_Loader_FilesystemLoader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

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
    public function boardAction(): Response
    {
        $repositories = explode(',', $_ENV['GH_REPOSITORIES']);

        $git = $this->gitFactory->createDefault();

        $milestones = [];

        foreach ($repositories as $repository) {
            $milestones = array_merge($milestones, $git->getMilestones($repository));
        }

        $engine = new Mustache_Engine([
            'loader' => new Mustache_Loader_FilesystemLoader(
                dirname(__FILE__) . '/../../views'
            ),
        ]);

        return new Response(
            $engine->render('index', ['milestones' => $milestones])
        );
    }
}
