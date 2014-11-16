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
                    'database-create-indexes', "\n",
                    'database-migrate-dburi', "\n",
                    'queue-peek', "\n",
                    'search-reindex [collection]', "\n",
                    'search-index-drop', "\n",
                    'topics-show', "\n",
                    'version', "\n",
                    'worker', "\n";
                break;

            case 'manager-install':
                if (!isset($_SERVER['argv'][2])) {
                    return;
                }
                $container->build->managerInstall($_SERVER['argv'][2]);
                break;

            case 'collection-install':
                if (!isset($_SERVER['argv'][2])) {
                    return;
                }
                $container->build->collectionInstall($_SERVER['argv'][2]);
                break;

            case 'build':
                echo shell_exec('cd ' . $root . '/.. && composer dump-autoload');
                $container->build->project($root);
                break;

            case 'queue-peek':
                $container->queue->peekReady();
                break;

            case 'worker':
                set_time_limit(0);
                $container->worker->work();
                break;

            case 'check':
                $container->build->environmentCheck($root);
                break;

            case 'database-migrate-dburi':
                $container->dbmigration->addURI();
                break;

            case 'database-create-indexes':
                $container->collectionModel->reIndexDataAll();
                break;

            case 'search-reindex':
                if (isset($_SERVER['argv'][2])) {
                    $container->collectionModel->reIndexSearch($_SERVER['argv'][2]);
                } else {
                    $container->collectionModel->reIndexSearchAll();
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
                echo file_get_contents($root . '/../vendor/opine/framework/version.txt'), "\n";
                break;

            default:
                echo 'Unknown command', "\n";
                break;
        }
    }
}