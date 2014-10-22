<?php
namespace Opine;

class CommandLine {
	public function run () {
        if (!isset($_SERVER['argv'][1])) {
            die('no command supplied');
        }
        $framework = new Framework(true);
        $container = $framework->container();
        $framework->routing();
        $root = $framework->root();
        switch ($_SERVER['argv'][1]) {
            case 'help':
                echo 
                    'The available commands are:', "\n",
                    'build', "\n",
                    'check', "\n",
                    'collection-counts-refresh', "\n",
                    'container-build', "\n",
                    'database-migrate-dburi', "\n",
                    'search-reindex [collection]', "\n",
                    'search-index-drop', "\n",
                    'topics-show', "\n",
                    'upgrade', "\n",
                    'version', "\n",
                    'worker', "\n";
                break;

            case 'build':
                $container->build->project($root);
                break;

            case 'worker':
                set_time_limit(0);
                $container->topic->load($root);
                $container->worker->work();
                break;

            case 'upgrade':
                $container->build->upgrade($root);
                break;

            case 'check':
                $container->build->environmentCheck($root);
                break;

            case 'database-migrate-dburi':
                $container->dbmigration->addURI();
                break;

            case 'search-reindex':
                if (isset($_SERVER['argv'][2])) {
                    $container->collectionModel->reIndex($_SERVER['argv'][2]);
                } else {
                    $container->collectionModel->reIndexAll();
                }
                break;

            case 'search-index-drop':
                $container->search->indexDrop();
                break;

            case 'topics-show':
                $container->topic->show();
                break;

            case 'collection-counts-refresh':
                $container->collectionModel->statsAll();
                break;

            case 'container-build':
                $container->build->container($root);
                break;

            case 'version':
                echo 'Unknown', "\n";
                break;
        }
    }
}