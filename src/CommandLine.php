<?php
namespace Opine;

class CommandLine {
	public function run () {
        if (!isset($_SERVER['argv'][1])) {
            die('no command supplied');
        }
        $framework = new Framework();
        $container = $framework->container();
        $framework->cache();
        $framework->routing();
        $root = $framework->root();
        switch ($_SERVER['argv'][1]) {
            case 'help':
                echo 
                    'The available commands are:', "\n",
                    'build', "\n",
                    'worker', "\n",
                    'upgrade', "\n",
                    'check', "\n",
                    'dburi', "\n",
                    'reindex', "\n",
                    'topics', "\n",
                    'count', "\n";
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

            case 'dburi':
                $container->dbmigration->addURI();
                break;

            case 'reindex':
                break;

            case 'topics':
                $container->topic->show();
                break;

            case 'count':
                $container->collection->statsAll();
                break;
        }
    }
}