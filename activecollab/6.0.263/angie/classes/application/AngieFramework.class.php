<?php

/*
 * This file is part of the ActiveCollab project.
 *
 * (c) A51 doo <info@activecollab.com>. All rights reserved.
 */

use Angie\Events;
use Angie\Inflector;

/**
 * Foundation that all angie framework definitions extend.
 *
 * @package angie.library.application
 */
abstract class AngieFramework
{
    const INJECT_INTO = 'system';

    /**
     * Short name of the framework.
     *
     * @var string
     */
    protected $name;

    /**
     * Framework's version.
     *
     * @var string
     */
    protected $version = '1.0';

    /**
     * Initialize framework.
     */
    public function init()
    {
        $this->defineClasses();
    }

    /**
     * Define classes used by this framework.
     */
    public function defineClasses()
    {
    }

    /**
     * Define framework handlers.
     */
    public function defineHandlers()
    {
    }

    public function defineListeners(): array
    {
        return [];
    }

    /**
     * Subscribe $callback function to $event.
     *
     * @param string         $event
     * @param Closure|string $callback
     */
    protected function listen($event, $callback = null)
    {
        if (empty($callback)) {
            $callback = "$this->name/$event";
        } else {
            if (is_string($callback) && strpos($callback, '/') === false) {
                $callback = "$this->name/$callback";
            }
        }

        Events::listen($event, $callback);
    }

    // ---------------------------------------------------
    //  Paths
    // ---------------------------------------------------

    /**
     * Return framework name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return framework version.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Return full framework path.
     *
     * @return string
     */
    public function getPath()
    {
        return ANGIE_PATH . '/frameworks/' . $this->name;
    }

    // ---------------------------------------------------
    //  Model and installation
    // ---------------------------------------------------

    /**
     * Cached model instance.
     *
     * @var AngieFrameworkModel
     */
    private $model = false;

    /**
     * Return model definition for this framework / module.
     *
     * @return AngieFrameworkModel
     */
    public function &getModel()
    {
        if ($this->model === false) {
            $model_class = get_class($this) . 'Model';
            $model_file = $this->getPath() . "/resources/$model_class.class.php";

            // Load file and create instance
            if (is_file($model_file)) {
                require_once $model_file;
                $this->model = new $model_class($this);
            }

            if (!($this->model instanceof AngieFrameworkModel)) {
                $this->model = null;
            }
        }

        return $this->model;
    }

    /**
     * Install this framework.
     */
    public function install()
    {
        if ($this->getModel() instanceof AngieFrameworkModel) {
            $this->getModel()->createTables();
            $this->getModel()->loadInitialData();
        }
    }

    // ---------------------------------------------------
    //  Path resolution and loading
    // ---------------------------------------------------

    /**
     * Load controller class.
     *
     * @param  string       $controller_name
     * @return string
     * @throws FileDnxError
     */
    public function useController($controller_name)
    {
        $controller_class = Inflector::camelize($controller_name) . 'Controller';
        if (!class_exists($controller_class, false)) {
            $controller_file = $this->getPath() . "/controllers/$controller_class.class.php";

            if (is_file($controller_file)) {
                include_once $controller_file;
            } else {
                throw new FileDnxError($controller_file, "Controller $this->name::$controller_name does not exist (expected location '$controller_file')");
            }
        }

        return $controller_class;
    }

    /**
     * Use specific helper.
     *
     * @param  string       $helper_name
     * @param  string       $helper_type
     * @return string
     * @throws FileDnxError
     */
    public function useHelper($helper_name, $helper_type = 'function')
    {
        if (!function_exists("smarty_{$helper_type}_{$helper_name}")) {
            $helper_file = $this->getPath() . "/helpers/$helper_type.$helper_name.php";

            if (is_file($helper_file)) {
                include_once $helper_file;
            } else {
                throw new FileDnxError($helper_file, "Helper $this->name::$helper_name does not exist (expected location '$helper_file')");
            }
        }

        return "smarty_{$helper_type}_{$helper_name}";
    }

    /**
     * Use specific model.
     *
     * $model_names can be single model name or array of model names
     *
     * @param string|string[] $model_names
     */
    public function useModel($model_names)
    {
        foreach ((array) $model_names as $model_name) {
            $object_class = Inflector::camelize(Inflector::singularize($model_name));
            $manager_class = Inflector::camelize($model_name);

            AngieApplication::setForAutoload([
                "Base$object_class" => $this->getPath() . "/models/$model_name/Base$object_class.class.php",
                $object_class => $this->getPath() . "/models/$model_name/$object_class.class.php",
                "Base$manager_class" => $this->getPath() . "/models/$model_name/Base$manager_class.class.php",
                $manager_class => $this->getPath() . "/models/$model_name/$manager_class.class.php",
            ]);
        }
    }

    /**
     * Use specific model.
     *
     * $model_names can be single model name or array of model names
     *
     * @param string|string[] $view_names
     */
    public function useView($view_names)
    {
        foreach ((array) $view_names as $view_name) {
            $view_class = Inflector::camelize($view_name);

            AngieApplication::setForAutoload([
                "Base{$view_class}" => $this->getPath() . "/models/$view_name/Base{$view_class}.class.php",
                $view_class => $this->getPath() . "/models/$view_name/$view_class.class.php",
            ]);
        }
    }

    /**
     * Return proxy URL.
     *
     * @param  string $proxy
     * @param  mixed  $params
     * @return string
     */
    public function getProxyUrl($proxy, $params = null)
    {
        if (empty($params)) {
            $url_params = [
                'proxy' => $proxy,
                'module' => $this->getName(),
                'v' => AngieApplication::getVersion(),
                'b' => AngieApplication::getBuild(),
            ];
        } else {
            $url_params = array_merge([
                'proxy' => $proxy,
                'module' => $this->getName(),
                'v' => AngieApplication::getVersion(),
                'b' => AngieApplication::getBuild(),
            ], $params);
        }

        return ROOT_URL . '/proxy.php?' . (version_compare(PHP_VERSION, '5.1.2', '>=') ? http_build_query($url_params, '', '&') : http_build_query($url_params, ''));
    }

    /**
     * Return email template path.
     *
     * @param  string $template
     * @return string
     */
    public function getEmailTemplatePath($template)
    {
        return $this->getPath() . "/email/$template.tpl";
    }

    /**
     * Return path of file where specific event handler is defined.
     *
     * @param  string $callback_name
     * @return string
     */
    public function getEventHandlerPath($callback_name)
    {
        return $this->getPath() . "/handlers/$callback_name.php";
    }
}
