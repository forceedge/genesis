<?php

namespace Application\Console;



class Initializer{

    public static function init($switch)
    {
        $args = explode(':', $switch);

        switch (strtolower($args[0]))
        {
            case 'bundle':
            {
                self::BundleOptions($args);
                break;
            }

            case 'schema':
            {
                self::SchemaOptions($args);
                break;
            }

            case 'test':
            {
                self::TestOptions($args);
                break;
            }

            case 'cache':
            {
                self::CacheOptions($args);
                break;
            }

            case 'component':
            {
                self::ComponentsOptions($args);
                break;
            }

            case 'help':
            {
                Console::HowToUse();
                Console::Legend();
                break;
            }

            case 'exit':
            {
                echo 'Exiting';
                exit;
            }
        }
    }

    private static function SchemaOptions($args)
    {
        $schema = new Lib\SchemaUI();

        switch($args[1])
        {
            case 'drop':
            {
                $database = null;
                if(isset($args[2]))
                    $database = $args[2];

                $schema->Drop($args[2]);
                break;
            }

            case 'export':
            {
                $database = null;
                if(isset($args[2]))
                    $database = $args[2];

                $schema->exportDefinition($database);
                break;
            }

            case 'import':
            {
                $schema->import($args[2]);
                break;
            }
        }
    }

    private static function ComponentsOptions($args)
    {
        $component = new Lib\ComponentsUI();

        switch($args[1])
        {
            case 'create':
            {
                $component->Create();
                break;
            }
            case 'delete':
            {
                $component->Delete();
                break;
            }
        }
    }

    private static function BundleOptions($args)
    {
        if (isset($_SERVER['SERVER_NAME']))
        {
            $bundle = new Lib\BundleUI('html');
            $bundle->name = ucfirst(str_replace('bundle', '', strtolower(($_POST['bundle'] ? $_POST['bundle'] : $_POST['bundleName'][0] ))));
        }
        else
        {
            $bundle = new Lib\BundleUI('console');
        }

        switch ($args[1])
        {
            case 'create':
            {
                $bundle->createBundle();
                break;
            }
            case 'delete':
            {
                $bundle->deleteBundle();
                break;
            }
            case 'assets':
            {
                if($args[2] == 'create')
                {
                    $bundle->InitCreateAssets();
                }
                else if($args[2] == 'delete')
                {
                    $bundle->DeleteAssets();
                }
                break;
            }
            case 'verify':
            {
                $bundle->Check();
                break;
            }
            case '0':
            case 'exit':
            {
                exit(0);
                break;
            }
        }
    }

    private static function TestOptions($args)
    {
        $this->object = New Lib\Test();
        Lib\Test::$output = $_SERVER['argv'][2];

        switch($args[1])
        {
            case 'routes':
            {
                $this->object->RunTests('route');
                break;
            }

            case 'classes':
            {
                $this->object->RunTests('class');
                break;
            }

            case 'methods':
            {
                $this->object->RunTests('method');
                break;
            }

            case 'templates':
            {
                $this->object->RunTests('template');
                break;
            }

            case 'models':
            {
                $this->object->RunTests('model');
                break;
            }

            case 'all':
            {
                if(!is_object($this->object))
                    $this->object = new Test();

                $this->object->RunTests();

                $this->object->clearResults();
                break;
            }
        }
    }

    private static function CacheOptions($args)
    {
        require_once \Get::Config('APPDIRS.CORE.LIB_FOLDER') . 'Debugger.Class.php';
        require_once \Get::Config('APPDIRS.COMPONENTS.BASE_FOLDER') . 'Directory/Directory.Class.php';

        $cache = new Lib\CacheUI();
        switch($args[1])
        {
            case 'clear':
            {
                $cache->Clear();
                break;
            }
        }
    }
}