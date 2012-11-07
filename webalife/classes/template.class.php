<?php
/**
 * Webalife.CMS
 */
class template
{
    private $smarty;

    function __construct()
    {
        require_once(SMARTY_DIR . 'Smarty.class.php');
        // Smarty initialize
        $smarty = new Smarty();
        $smarty->setTemplateDir(CMS_TEMPLATE_PATH . 'globals/');
        $smarty->setCompileDir(CMS_RUNTIME_PATH . 'tpl_compiles/');
        $smarty->setCacheDir(CMS_RUNTIME_PATH . 'tpl_cache/');
        $smarty->setPluginsDir(SMARTY_DIR.'/plugins');
        $smarty->addPluginsDir(CMS_MODULES);
        $smarty->compile_check = true;
        $smarty->cache_id = $_SERVER['REQUEST_URI'];
        $smarty->compile_id = $_SERVER['HTTP_HOST'];
        $this->smarty = $smarty;
    }

    function assign($name, $value = null)
    {
        $this->smarty->assign($name, $value);
    }

    function display($template)
    {
        $this->smarty->display($template . '.tpl');
    }

    function fetch($template) {
        return $this->smarty->fetch($template.'.tpl');
    }

    /**
     * @return Smarty
     */
    function getSmarty()
    {
        return $this->smarty;
    }
}
