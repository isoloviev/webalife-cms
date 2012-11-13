<?php
/**
 * Webalife.CMS Core
 *
 * ------- DON'T MODIFY THIS FILE!!! -------
 */

// do some with language of site
if ($_REQUEST['lang']) $_SESSION['SiteLang'] = $_REQUEST['lang']; else $_SESSION['SiteLang'] = DEFAULT_LANG;

if ($_SESSION['SiteLang'] != DEFAULT_LANG) {
    define("SITELANG", $_SESSION['SiteLang']);
    define("HTMLLANG", $_SESSION['SiteLang'] . "/");
} else {
    define("SITELANG", $_SESSION['SiteLang']);
    define("HTMLLANG", "");
}
unset($_SESSION['REQUEST_PARAMS']);

// include templater
include_once(CMS_CLASSES . 'template.class.php');
// include mysql class
include_once(CMS_CLASSES . "mysql.class.php");
// include language file
include_once(CMS_LANGUAGES . "language_" . SITELANG . ".php");

class site
{
    // Current version of CMS
    var $CMS_VERSION = 'Webalife.CMS';
    var $TEMPLATE_PATH = '';
    var $cache_id = '';
    var $WithOut = false;

    /**
     * @var template
     */
    private $tpl;

    /* Инициализация соединения с базой данных */
    function site($connect = true)
    {
        global $mysql, $db, $CMS_LANG;
        // some defines
        define('CMS_VERSION', $this->CMS_VERSION);

        // Smarty initialize
        $this->tpl = new template();

        $mysql = $db = new mysql();
        try {
            $mysql->connect();
        } catch (Exception $e) {
            $this->tpl->assign("message", $e->getMessage());
            $this->tpl->assign("code", $e->getCode());
            $this->tpl->display("error");
            exit;
        }

        if ($connect) {
            // пробиваем данные по хосту
            $res = $mysql->sql("SELECT * FROM " . PREFIX . "sites WHERE domain = '" . $_SESSION['CMS_HOST'] . "' OR aliases LIKE '%" . $_SESSION['CMS_HOST'] . "%'", 1);
            if (!is_array($res)) {
                // если не найдено, запускаем сайт по умолчанию
                $res = $mysql->sql("SELECT * FROM " . PREFIX . "sites WHERE `default` = 1", 1);
                foreach ($res as $key => $value) {
                    $_SESSION['CMS_' . $key] = $value;
                }
            } else {
                foreach ($res as $key => $value) {
                    $_SESSION['CMS_' . $key] = $value;
                }
            }
        }

        // look for modules
        $res = $mysql->sql("SELECT prefix FROM " . PREFIX . "modules", 2);
        $mods[] = CMS_MODULES;
        foreach ($res as $r) $mods[] = CMS_MODULES . $r['PREFIX'] . '/';

        $this->tpl->getSmarty()->addTemplateDir(CMS_TEMPLATE_PATH . $_SESSION['CMS_THEME'] . '/');
        $this->tpl->getSmarty()->compile_id = $_SESSION['CMS_DOMAIN'];


        foreach ($mods as $m)
            $this->tpl->getSmarty()->addPluginsDir($m);

        if (!$_SESSION['InAdminSession']) {
            $this->caching = true;
        }
        $this->tpl->getSmarty()->cache_lifetime = 360000;

        // инциализация администраторской сессии
        $this->tpl->assign('InAdminSession', ($_SESSION['InAdminSession'] ? 'true' : 'false'));

        // assign language data
        $this->tpl->assign('Lang', $CMS_LANG);
        $this->tpl->assign('SLANG', HTMLLANG);

        // биндим пути
        $this->tpl->assign('CMS_ROOT_URL', $_SERVER['CMS_ROOT_URL']);
        $this->tpl->assign('CMS_ADMIN_URL', $_SERVER['CMS_ADMIN_URL']);

        // биндим языки
        $this->tpl->assign(array("CurrentLang" => SITELANG, "SLANG", HTMLLANG));

        // определяем текущую группу пользователя, если не заявлено другое
        if (!$_SESSION['USER_GROUP']) {
            $r = $mysql->sql("SELECT group_id id FROM " . PREFIX . "users_groups WHERE `default` = 1", 1);
            $_SESSION['USER_GROUP'] = $r['ID'];
        }

        // assign basket content
        $this->tpl->assign('basket', $_SESSION['basket']);
    }

    /* Конструктор сайта */
    function main()
    {
        global $mysql, $SITE, $page_path, $page_type, $cms_record_id;
        // инициализация параметров и урл
        if (!$this->_DetectURL($this->get_url())) return $this->PageNotFound();
        // проверим, а вдруг имеется обрабочики заголовков для модулей
        if ($_REQUEST['module'] != "") {
            $this->HeaderHandler();
        }
        // биндим GET параметры
        foreach ($_GET as $key => $value) $g[$key] = $value;
        if (is_array($g)) $this->tpl->assign("GET", $g);
        // биндим POST параметры
        foreach ($_POST as $key => $value) $p[$key] = $value;
        if (is_array($p)) $this->tpl->assign("POST", $p);
        // переопределяем текущий адрес страницы
        $page_path = $this->get_url();
        // содержание страницы, в зависимости от выбранного языка
        $page = $mysql->sql("SELECT p.*, c.* FROM " . PREFIX . "pages p, " . PREFIX . "pages_content c WHERE p.page_id = c.page_id AND c.lang = '" . SITELANG . "' AND p.page_path = '" . $page_path . "' AND p.site_id = " . $_SESSION['CMS_SITE_ID'], 1);
        //echo $mysql->getsql();
        if (!is_array($page)) return $this->PageNotFound();
        if (strlen($page['REDIR_TO']) && ($page['REDIR_TO'] != '0')) return $this->PageRedirect($page['REDIR_TO']);
        // переопределяем некоторые переменные
        $SITE['Page_Type'] = $page_type = $page['PAGE_TYPE'];
        // название сайта
        if ($_SESSION['CMS_NAME_RU'] != "") $SITE['SiteName'] = ' &mdash; ' . $_SESSION['CMS_NAME_RU'];
        $SITE['Copyright'] = $_SESSION['CMS_COPYRIGHT_RU'];
        // вытаскиваем связи модулей со страницами
        if ($page['RELATIONS']) foreach (unserialize($page['RELATIONS']) as $r) define('cms_' . $r, 'true');
        $cms_record_id = $page['RECORD_ID'];
        $SITE['PageTitle'] = $page['PAGE_TITLE'];
        $SITE['Description'] = $page['PAGE_DESC'];
        $SITE['Keywords'] = $page['PAGE_KEYS'];
        $SITE['Content'] = $page['PAGE_CONTENT'];
        $SITE['Header'] = $page['HEADER_TITLE'];
        $SITE['MailTo'] = $_SESSION['CMS_ADMIN_EMAIL'];
        $SITE['PAGE_ID'] = $page['PAGE_ID'];
        $SITE['PAGE_PRINT'] = $page['PAGE_PRINT'];
        $SITE['PAGE_PATH'] = $page['PAGE_PATH'];
        $SITE['URL'] = $_SERVER['REQUEST_URI'];
        // узнаем, есть ли дочерние страницы
        $res = $mysql->sql("SELECT count(*) cnt FROM " . PREFIX . "pages WHERE page_parent = " . $SITE['PAGE_ID'] . " AND site_id = " . $_SESSION['CMS_SITE_ID'], 1);
        $SITE['LEVELS'] = $res['CNT'];
        // подключение определенного шаблона-конструктора
        if ($page['PAGE_TMPLT'] == "") $page['PAGE_TMPLT'] = "default";
        // делаем куррент пас
        $PregPath = str_replace('/', '-', $SITE['PAGE_PATH']);
        $PregPath = ereg_replace('^-', '/', $PregPath);
        $PregPath = ereg_replace('-$', '', $PregPath);
        if ($PregPath != '/') $PregPath .= '.html'; else $PregPath = '/';
        $CurrentPage = $PregPath;
        define('CURRENT_PATH', $_SERVER['CMS_ROOT_URL'] . SITELANG . $PregPath);
        $this->tpl->assign('CURRENT_PATH', CURRENT_PATH);
        $this->tpl->assign('CMS_VERSION', CMS_VERSION);
        // админские превращения
        if ($_SESSION['InAdminSession']) {
            define('InAdminSession', true);
        }
        if (file_exists($this->template_dir . "page." . $page['PAGE_TMPLT']))
            $tmpname = "page." . $page['PAGE_TMPLT'];
        else
            $tmpname = "page.default";

        // parsing dynamic content, as possible
        $SITE['Content'] = $this->ParseContent($SITE['Content']);

        // версия для печати
        if ($_SESSION['REQUEST_PARAMS'][0] == "printmode") $tmpname = "page.print";

        // список сайтов
        $sites = $mysql->sql("SELECT * FROM " . PREFIX . "sites ORDER BY `default` DESC, binary(domain)", 2);
        foreach ($sites as $s) {
            $si[] = array('SITE_ID' => $s['SITE_ID'], 'DOMAIN' => $s['DOMAIN'], 'NAME' => $s['NAME']);
        }
        $this->tpl->assign('ALL_SITES', $si);

        // check for access to this page
        if (!$this->PageAccess($cms_record_id)) $SITE['Content'] = 'Access denied!';
        // assign some elements
        $this->tpl->assign('Page', $SITE);

        // путь к картинкам раздела
        $tmp = ereg_replace('^/', '', $SITE['PAGE_PATH']);
        $tmp = ereg_replace('/$', '', $tmp);
        $tmp = str_replace('/', '-', $tmp);
        if (!$tmp) $tmp = 'index';
        if (!file_exists(CMS_ROOT_DIR . 'images/red/title_' . $tmp . '.gif')) {
            $tmp = ereg_replace('\-([0-9A-Za-z_]+)$', '', $tmp);
        }
        $this->tpl->assign('PATH_TO', $tmp);

        $this->tpl->assign('IMAGE_THEME', $_SESSION['IMAGE_THEME']);

        // parse & display template of site
        if (!$this->WithOut)
            $buffer = $this->tpl->fetch($tmpname);
        else
            $buffer = $this->tpl->fetch('body1');

        if (strlen(QUICK_LINKS) > 12) {
            $buffer = str_replace('[LINK_QUICK]', QUICK_LINKS, $buffer);
            $buffer = str_replace('[LINK_START]', '<a href="' . $this->PrepareURL($this->get_url()) . '">', $buffer);
            $buffer = str_replace('[LINK_END]', '</a>', $buffer);
        } else {
            $buffer = str_replace('[LINK_QUICK]', '', $buffer);
            $buffer = str_replace('[LINK_START]', '', $buffer);
            $buffer = str_replace('[LINK_END]', '', $buffer);
        }

        echo $buffer;
    }

    function mainWithout()
    {
        $this->WithOut = true;
        $this->main();
    }

    function DetectParent($pid, $stopp = '')
    {
        global $mysql;
        $res = $mysql->sql("SELECT page_parent pp, page_id pid FROM " . PREFIX . "pages WHERE page_id = " . $pid, 1);
        if ($stopp == $res['PP']) return $res['PID'];
        if ($res['PP'] > 1)
            return $this->DetectParent($res['PP'], $stopp);
        else
            return $res['PID'];
    }

    /* Dynamic's Parser */
    function ParseContent($html = '')
    {
        // проверим на наличие contrib
        $handle = @opendir($GLOBALS['CMS_ROOT_DIR'] . 'contrib/');
        if ($handle) $html = $this->msg_info('Внимание! Хранение каталога с инсталлятором является не безопасным!<br/>Удалите каталог ' . CMS_ROOT_DIR . 'contrib/') . $html;
        // что заменить
        $patterns = array("\{\%ARTICLE\:", "\{\%FORM\:", "\{\%NEWS\:",); //"\{\%POLL\:");
        // если нужно что-то убрать
        $replacements[$patterns[0]] = 'DOC';
        $replacements[$patterns[2]] = 'FEED';
        // какой метод запускать при совпадении
        $functions[$patterns[0]] = 'ArticleGenerator';
        $functions[$patterns[1]] = 'FormGenerator';
        $functions[$patterns[2]] = 'NewsGenerator';
        $functions[$patterns[3]] = 'PollGenerator';

        foreach ($patterns as $pattern) {
            $cnt = split($pattern, $html);
            for ($i = 0; $i < count($cnt); $i++) {
                $strl = ($i > 0 ? strlen(stripslashes($pattern)) : 0);
                $StrAll += strlen($cnt[$i - 1]) + $strl;
                $part = substr($html, $StrAll - $strl, strlen($cnt[$i]) + $strl);
                if (eregi($pattern . '([a-z0-9A-Z]+)\%\}', $part)) {
                    $begin = stristr($part, stripslashes($pattern));
                    $pos = strpos($begin, '%}');
                    $begin = trim(substr($begin, 0, $pos + 2));
                    $doc_id = eregi_replace('^' . $pattern . '|\%\}$', '', $begin);
                    $doc_id = str_replace($replacements[$pattern], '', $doc_id);
                    $part = eregi_replace($pattern . '([a-z0-9A-Z]+)\%\}', call_user_method_array($functions[$pattern], $this, array($doc_id, $part)), $part);
                }
                $content[] = $part;
            }
            $html = implode('', $content);
            $content = array();
            $StrAll = 0;
        }
        return $html;
    }

    /* Построение карты сайта */
    function SiteMap($parent = 0, $cnt = -1)
    {
        global $mysql, $ret;
        $category_path = $this->get_url();
        $menu = $mysql->sql("SELECT c.menu_title name_menu, p.page_path, p.page_parent pid, p.page_id id FROM " . PREFIX . "pages p, " . PREFIX . "pages_content c WHERE p.page_id = c.page_id AND c.lang = '" . SITELANG . "' AND p.page_parent = " . $parent . " AND p.page_active = 1 AND p.page_type != 'auth' AND p.site_id = " . $_SESSION['CMS_SITE_ID'] . " ORDER BY page_order", 2);
        $cnt++;
        for ($i = 0; $i < count($menu); $i++) {
            $PagePath = $this->PrepareURL($menu[$i]['PAGE_PATH']);
            $ret[] = array("Text" => $menu[$i]['NAME_MENU'], "Link" => $PagePath, "Spacer" => $cnt);
            $this->SiteMap($menu[$i]['ID'], $cnt);
        }
        $cnt--;
        return $ret;
    }

    /* Query Form's Generator */
    function FormGenerator($form_id)
    {
        global $mysql, $CMS_LANG;
        if (strtolower($form_id) == $_SESSION['REQUEST_PARAMS'][0] && $_SESSION['REQUEST_PARAMS'][1] == 'sendok') $text[] = '<p align="center" style="color: #eee4c5;">' . $CMS_LANG['REQUEST_SEND'] . '!</p>';
        if (strlen(FORM_ERROR) > 10) $text[] = '<p style="color: red;">' . FORM_ERROR . '</p>';
        include_once(CMS_CLASSES . 'forms.class.php');
        $form = new GenForms();
        // смотрим активна ли она или что-то там еще
        $fdata = $mysql->sql("SELECT * FROM " . PREFIX . "forms WHERE name_id = '" . $form_id . "'", 1);
        if ($fdata['ACTIVE'] != 1) return '';
        // получим список полей данной формы
        $elements = $form->GetElements($form_id);
        if ($form_id != 'Order' && $form_id != 'FeedBack') $text[] = '<div><b>' . $fdata['NAME'] . '</b></div><br/>';
        foreach ($elements as $r) {
            if ($r['TYPE'] != "select" && $r['TYPE'] != 'radio') {
                $addhtml = ' style="width: 100%;"';
                if ($r['TYPE'] == 'email') $r['TYPE'] = 'text';
                if ($r['TYPE'] == 'textarea') $addhtml = ' rows="5" cols="20" style="width: 100%; height: 150px; background-image: none;"';
                $le = array("name" => $r['NAME'],
                    "value" => '',
                    "type" => $r['TYPE'],
                    "extrahtml" => (!eregi('radio|checkbox', $r['TYPE']) ? 'class="field"' . $addhtml : $addhtml));
                if ($r['MAIN'] == 1) {
                    $le["minlength"] = 1;
                    $le["length_e"] = $CMS_LANG['FILL_FIELD'];
                }
                $form->add($le);
                unset($le);
            } elseif ($r['TYPE'] == "select") {
                $options = $form->GetOptions(1, $r['GROUP_ID']);
                $form->add(array("name" => $r['NAME'], "type" => $r['TYPE'], "value" => '', "options" => $options, "extrahtml" => 'class="field" style="width: 100%;"'));
            } elseif ($r['TYPE'] == "radio") {
                $options = $form->GetOptions(1, $r['GROUP_ID'], 'radio');
                $foo = 0;
                foreach ($options as $v) {
                    $form->add(array("name" => $r['NAME'] . '[]', "type" => $r['TYPE'], "value" => $v));
                    $foo++;
                }
            }
        }
        $TmpUrl = $_REQUEST['url'];
        if (strpos($TmpUrl, '.')) $TmpUrl = substr($TmpUrl, 0, strpos($TmpUrl, '.'));
        $text[] = $form->get_start($form_id, "post", $_SERVER['CMS_ROOT_URL'] . SITELANG . '/' . $TmpUrl . '.html', "", $form_id);

        // генерим цифровой код
        include_once(CMS_CLASSES . 'anti.php');

        if ($form_id == 'FeedBack') {
            // теперь выводим все элементы формы
            $text[] = '<table cellspacing="0" cellpadding="0" border="0" width="100%">';
            $text[] = '<tr><td width="50%" style="padding-right: 5px;"><table cellspacing="0" cellpadding="3" border="0" width="100%">';
            foreach ($elements as $r) {
                if ($r['NAME'] == 'Message') $text[] = '</table></td><td style="padding-left: 5px;"><table cellspacing="0" cellpadding="3" border="0" width="100%">';
                if ($r['TYPE'] != 'radio') $text[] = '<tr><td valign="top">' . $r['TEXT'] . ($r['MAIN'] == 1 ? '<font color="red">*</font>' : '') . ':</td></tr>';
                $text[] = '<tr><td>';
                if ($r['TYPE'] == 'radio') {
                    for ($t = 0; $t < $foo; $t++) {
                        $text[] = $form->get($r['NAME'] . '[]') . ' ' . $options[$t] . '<br>';
                    }
                } else {
                    $text[] = $form->get($r['NAME']);
                }
                $text[] = '</td></tr>';
            }
            $anti = new AntiSpam();
            $code = base64_encode($anti->Rand(5));
            $text[] = '<tr><td>Введите число, указанное на картинке<font color="red">*</font>:</td></tr>';
            $text[] = '<tr><td><img src="/as.php?code=' . $code . '" alt="" border="0" style="float: left; margin-right: 10px;"><input type="text" name="FormCode" value="" class="field" style="width: 93px;" maxlength="5"><input type="hidden" name="FormOriCode" value="' . $code . '"></td></tr>';
            $text[] = '</table></td></tr><tr><td colspan="2" align="center" style="padding-top: 10px;"><input type="hidden" name="form_id" value="' . $form_id . '"><input type="submit" name="SendForm" value="' . $CMS_LANG['SUBMIT_BUTTON'] . '" class="submit_main" style="width: auto;"></td></tr>';
            $text[] = '</table>';
        } else {
            // теперь выводим все элементы формы
            $text[] = '<table cellspacing="0" cellpadding="3" border="0" width="100%">';
            foreach ($elements as $r) {
                if ($r['TYPE'] != 'radio') $text[] = '<tr><td valign="top">' . $r['TEXT'] . ($r['MAIN'] == 1 ? '<font color="red">*</font>' : '') . ':</td></tr>';
                $text[] = '<tr><td>';
                if ($r['TYPE'] == 'radio') {
                    for ($t = 0; $t < $foo; $t++) {
                        $text[] = $form->get($r['NAME'] . '[]') . ' ' . $options[$t] . '<br>';
                    }
                } else {
                    $text[] = $form->get($r['NAME']);
                }
                $text[] = '</td></tr>';
            }
            $anti = new AntiSpam();
            $code = base64_encode($anti->Rand(5));
            $text[] = '<tr><td>' . $CMS_LANG['CAPTCHA'] . '<font color="red">*</font>:</td></tr>';
            $text[] = '<tr><td><img src="/as.php?code=' . $code . '" alt="" border="0" style="float: left; margin-right: 10px;"><input type="text" name="FormCode" value="" class="field" style="width: 93px;" maxlength="5"><input type="hidden" name="FormOriCode" value="' . $code . '"></td></tr>';
            $text[] = '<tr><td align="center"><input type="hidden" name="form_id" value="' . $form_id . '"><input type="submit" name="SendForm" value="' . $CMS_LANG['SUBMIT_BUTTON'] . '" class="submit_main" style="width: auto;"></td></tr>';
            $text[] = '</table>';
        }

        $text[] = $form->get_finish('', $after);
        return implode("\r\n", $text);
    }

    /* Article Generator */
    function ArticleGenerator($doc_id, $content)
    {
        global $mysql, $ArticleView;
        if (!$ArticleView) {
            $this->tpl->assign('ArticleCat', $doc_id);
            if (is_numeric($_SESSION['REQUEST_PARAMS'][1])) $ArticleView = true; else $ArticleView = false;
            return $this->fetch('articles/misc', $this->cache_id);
        } else {
            return '';
        }
    }

    /* News Generator */
    function NewsGenerator($feed_id, $content)
    {
        global $mysql, $NewsView;
        if (!$NewsView) {
            $this->tpl->assign('NewsFeed', $feed_id);
            if (is_numeric($_SESSION['REQUEST_PARAMS'][1])) $NewsView = true; else $NewsView = false;
            return $this->tpl->fetch('news/misc', $this->cache_id);
        } else {
            return '';
        }
    }

    /* Poll Generator */
    function PollGenerator($poll_id, $content)
    {
        global $mysql, $PollsView;
        if (!$PollsView) {
            $this->tpl->assign('PollID', $poll_id);
            if (is_numeric($_SESSION['REQUEST_PARAMS'][1])) $PollsView = true; else $PollsView = false;
            return $this->tpl->fetch('polls/misc', $this->cache_id);
        } else {
            return '';
        }
    }

    /* Составление списка элементов главного меню (передается шаблонизатору) */
    function MainMenu($parent = 0, $cnt = 0, $level = 0)
    {
        global $mysql, $cnt, $retTXT;
        $category_path = $this->get_url();
        $menu = $mysql->sql("SELECT c.menu_title name_menu, p.page_path, p.page_parent pid, p.page_id id, c.record_id rid FROM " . PREFIX . "pages p, " . PREFIX . "pages_content c WHERE p.page_id = c.page_id AND c.lang = '" . SITELANG . "' AND p.page_parent = " . $parent . " AND p.page_active = 1 AND p.page_type != 'auth' AND p.site_id = " . $_SESSION['CMS_SITE_ID'] . " ORDER BY page_order", 2);
        //echo $mysql->getsql().'<br/>';
        for ($i = 0; $i < count($menu); $i++) {
            if (eregi($menu[$i]['PAGE_PATH'], $this->get_url()))
                $active = true;
            else
                $active = false;
            $PagePath = $this->PrepareURL($menu[$i]['PAGE_PATH']);
            if ($this->PageAccess($menu[$i]['RID'])) {
                $retTXT[] = array("Text" => $menu[$i]['NAME_MENU'], "Link" => $PagePath, "Menu" => ereg_replace('/', '', $menu[$i]['PAGE_PATH']), "Active" => $active, "Spacer" => $cnt);
                if (ereg($menu[$i]['PAGE_PATH'], $this->get_url()) && $level > 1) {
                    if ($menu[$i]['ID'] > 1) $cnt += 5;
                    $this->MainMenu($menu[$i]['ID'], $cnt, $level);
                }
            }
        }
        $cnt -= 5;
        return $retTXT;
    }

    /* Быстрая навигация по сайту (передается шаблонизатору) */
    function ExtraMenu()
    {
        global $mysql;
        $CURRENT_URL = ereg_replace('/$', '', $this->get_url()); //str_replace('-','/',$GLOBALS['CMS_ROOT_URL'].eregi_replace('/$','',$_REQUEST['url']));
        $n = count(split('/', $CURRENT_URL));
        $rst = $mysql->sql("SELECT c.menu_title, p.page_path FROM " . PREFIX . "pages p, " . PREFIX . "pages_content c WHERE p.page_id = c.page_id AND c.lang = '" . SITELANG . "' AND p.page_path = '/' AND p.site_id = " . $_SESSION['CMS_SITE_ID'], 1);
        $retTXT[] = array("Text" => $rst['MENU_TITLE'], "Link" => $_SERVER['CMS_ROOT_URL'] . HTMLLANG);
        $path = "/";
        for ($i = 1; $i < $n; $i++) {
            $path .= $this->get_url_part($CURRENT_URL, $i) . "/";
            $menu = $mysql->sql("SELECT c.menu_title, p.page_path FROM " . PREFIX . "pages p, " . PREFIX . "pages_content c WHERE p.page_id = c.page_id AND c.lang = '" . SITELANG . "' AND p.page_path = '" . $path . "' AND p.site_id = " . $_SESSION['CMS_SITE_ID'], 1);
            $PagePath = $this->PrepareURL($menu['PAGE_PATH']);
            if ($i == ($n - 1)) {
                $retTXT[] = array("Text" => $menu['MENU_TITLE'], "Link" => "");
            } else {
                $retTXT[] = array("Text" => $menu['MENU_TITLE'], "Link" => $PagePath);
            }
        }
        return $retTXT;
    }

    /* Возвращает путь указанной страницы */
    function get_url()
    {
        global $category_path;
        $path = str_replace('-', '/', '/' . preg_replace('#/$|^/#i', '', $_SERVER['REQUEST_URI']));
        if ($path == "/" || $path == "" || $path == "\\") {
            $category_path = "/";
        } else {
            $category_path = $path . "/";
        }
        // выделим часть параметров и основного урла
        $pos = strpos($category_path, '.');
        if ($pos) {
            $params = str_replace('/', '', substr($category_path, $pos));
            $category_path = str_replace($params, '', $category_path);
            $_SESSION['REQUEST_PARAMS'] = preg_split('#,#', str_replace('.', '', $params));
            // bind to template
            $this->tpl->assign('RPARAM', $_SESSION['REQUEST_PARAMS']);
        }
        if ($category_path == '/index/') $category_path = '/';
        return $category_path;
    }

    /* Возвращает часть пути  */
    function get_url_part($str, $i, $multiple = false)
    {
        $arr = split("/", $str);
        if ($multiple) {
            for ($q = 0; $q < $i; $q++) $url[] = $arr[$q];
            return implode('/', $url);
        } else return $arr[$i];
    }


    /* Возвращает отформатированную дату */
    function format_date($string, $time, $lng = '')
    {
        if (!$lng) $lng = SITELANG;
        $param = $string;
        $months['ru'] = array('января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');
        $months['en'] = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
        $days['ru'] = array('воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота');
        $daysAbbt['ru'] = array('вск', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб');

        if ($time == "rAM") return $months['ru'][intval($string) - 1];

        $date = getdate($time);


        $string = str_replace('rM', $months[$lng][$date['mon'] - 1], $string);
        $string = str_replace('rS', $months[$lng][$date['mon'] - 1], $string);
        $string = str_replace('rDA', $daysAbbt[$lng][$date['wday']], $string);
        $string = str_replace('rD', $days[$lng][$date['wday']], $string);

        if ($param == 'rM') return $date['mday'] . " " . $string . " " . $date['year'];

        return $string;
    }

    /* Преобразование изображения */
    function image_action($from, $to, $x = 100, $y = 75, $q = 80)
    {
        // размеры изображения
        $image_details = getimagesize($from);
        $x1 = $image_details[0];
        $y1 = $image_details[1];

        if ($x1 > $y1) {
            $y2 = $y;
            $x2 = floor($x1 / ($y1 / $y));

            if (@ImageCreateFromJPEG($from)) $i1 = @ImageCreateFromJPEG($from);
            if (@ImageCreateFromGIF($from)) $i1 = @ImageCreateFromGIF($from);
            if (@ImageCreateFromPNG($from)) $i1 = @ImageCreateFromPNG($from);
            $i2 = ImageCreateTrueColor($x2, $y2);
            $i3 = ImageCreateTrueColor($x, $y);

            $x3 = floor(($x2 - $x) / 2);

            ImageCopyResampled($i2, $i1, 0, 0, 0, 0, $x2, $y2, $x1, $y1);
            ImageCopyResampled($i3, $i2, 0, 0, $x3, 0, $x, $y, $x, $y2);
            ImageJpeg($i3, $to, $q);

            ImageDestroy($i1);
            ImageDestroy($i2);
            ImageDestroy($i3);
        } else {
            $x2 = $x;
            $y2 = floor($y1 / ($x1 / $x));

            if (@ImageCreateFromJPEG($from)) $i1 = @ImageCreateFromJPEG($from);
            if (@ImageCreateFromGIF($from)) $i1 = @ImageCreateFromGIF($from);
            if (@ImageCreateFromPNG($from)) $i1 = @ImageCreateFromPNG($from);
            $i2 = ImageCreateTrueColor($x2, $y2);
            $i3 = ImageCreateTrueColor($x, $y);

            $y3 = floor(($y2 - $y) / 2);

            ImageCopyResampled($i2, $i1, 0, 0, 0, 0, $x2, $y2, $x1, $y1);
            ImageCopyResampled($i3, $i2, 0, 0, 0, $y3, $x, $y, $x2, $y);
            ImageJpeg($i3, $to, $q);

            ImageDestroy($i1);
            ImageDestroy($i2);
            ImageDestroy($i3);
        }
    }

    /* Пропорциональное преобразование изображения */
    function image_action_proportional($from, $to, $size = 100, $mode = 'byside', $q = 100)
    {
        $imgs = getimagesize($from);
        $iw = $orw = $imgs[0];
        $ih = $orh = $imgs[1];
        $itype = $imgs['mime'];
        switch ($mode) {
            case "bywidth":
                $iw = $size;
                $ih = floor($orh / ($orw / $iw));
                break;
            case "byheight":
                $ih = $size;
                $iw = floor($orw / ($orh / $ih));
                break;
            default:
                if ($orw > $orh) {
                    $iw = ($size <= $orw ? $size : $orw);
                    $ih = floor($orh / ($orw / $iw));
                } else {
                    $ih = ($size <= $orh ? $size : $orh);
                    $iw = floor($orw / ($orh / $ih));
                }
                break;
        }
        if (eregi('jpeg', $itype)) $i1 = imagecreatefromjpeg($from);
        if (eregi('gif', $itype)) $i1 = imagecreatefromgif($from);
        if (eregi('png', $itype)) $i1 = imagecreatefrompng($from);
        $i2 = imagecreatetruecolor($iw, $ih);
        imagecopyresampled($i2, $i1, 0, 0, 0, 0, $iw, $ih, $orw, $orh);
        if (eregi('jpeg', $itype)) imagejpeg($i2, $to, $q);
        if (eregi('gif', $itype)) imagegif($i2, $to, $q);
        if (eregi('png', $itype)) imagepng($i2, $to, $q);
        imagedestroy($i1);
        imagedestroy($i2);
    }

    /* Проверка на правильность указания адреса эл. почты */
    function is_email($string)
    {
        // Remove whitespace
        $string = trim($string);

        $ret = ereg(
            '^([a-zA-Z0-9_]|\\-|\\.)+' .
                '@' .
                '(([a-z0-9_]|\\-)+\\.)+' .
                '[a-z]{2,4}$',
            $string);

        return ($ret);
    }

    /* Select from array */
    function select_from_array($values, $cur)
    {
        if (sizeof($values) == 0)
            return '';

        foreach ($values as $key => $value) {
            if ($key == $cur) $sel = "selected"; else $sel = "";
            $txt .= "<option value=\"$key\" $sel>$value</option>";
        }
        return $txt;
    }

    /* Проверка указанной даты */
    function check_date($day, $month, $year)
    {
        if ($day > 31 && $month == 1) return false;
        if ($month == 2) {
            if ($this->IsLeapYear($year) && $day > 29) return false;
            if (!$this->IsLeapYear($year) && $day > 28) return false;
        }
        if ($day > 31 && $month == 3) return false;
        if ($day > 30 && $month == 4) return false;
        if ($day > 31 && $month == 5) return false;
        if ($day > 30 && $month == 6) return false;
        if ($day > 31 && $month == 7) return false;
        if ($day > 31 && $month == 8) return false;
        if ($day > 30 && $month == 9) return false;
        if ($day > 31 && $month == 10) return false;
        if ($day > 30 && $month == 11) return false;
        if ($day > 31 && $month == 12) return false;
        return true;
    }

    /* Проверка на високосный год true - да, false - нет */
    function IsLeapYear($year)
    {
        $tmp1 = intval($year / 4);
        if (($tmp1 * 4) == $year)
            return true;
        else
            return false;
    }

    /* Преобразование строковой переменной */
    function my_str_replace($my_value, $isstring = 0)
    {
        $my_value = str_replace("\\\"", "&quot;", $my_value);
        $my_value = str_replace("\'", "&#39;", $my_value);
        $my_value = str_replace("<", "&lt;", $my_value);
        $my_value = str_replace(">", "&gt;", $my_value);
        if ($isstring) {
            $my_value = str_replace("\n", " ", $my_value);
        } else {
            $my_value = str_replace("\n", "<br />", $my_value);
        }
        ;
        $my_value = str_replace("\"", "&quot;", $my_value);
        $my_value = str_replace("'", "&#39;", $my_value);
        $my_value = str_replace("`", "&#96;", $my_value);
        $my_value = str_replace("\\", "&#92;", $my_value);
        $my_value = preg_replace("/[ \t]{2,}/", " ", $my_value);
        return trim($my_value);
    }

    /* Page not found - error 404 */
    function PageNotFound()
    {
        $this->tpl->assign('Page', array('Content' => 'Page not found on this server!', 'Title' => '404 : Page not found'));
        $this->tpl->display('page.error');
    }

    /* Redirect */
    function PageRedirect($page_id)
    {
        global $mysql;
        if (is_numeric($page_id)) {
            $p = $mysql->sql("SELECT page_path FROM " . PREFIX . "pages WHERE page_id = " . $page_id, 1);
            $PregPath = $this->PrepareURL($p['PAGE_PATH']);
            header("Location: " . $PregPath);
        } else {
            header("Location: " . $page_id);
        }
        exit;
    }

    /* Error builder */
    function ERR_INFO($string)
    {
        if (!is_array($string)) $err = $string;
        else $err = '<li>' . implode('</li><li>', $string) . '</li>';
        $this->tpl->assign('message', $err);
        return $this->tpl->fetch('alert-error');
    }

    /* Message builder */
    function MSG_INFO($string)
    {
        if (!is_array($string)) $err = $string;
        else $err = '<li>' . implode('</li><li>', $string) . '</li>';
        $this->tpl->assign('message', $err);
        return $this->tpl->fetch('alert-info');
    }

    /* If valid english letters */
    function is_latin($string)
    {
        $string = trim($string);
        for ($i = 0; $i < strlen($string); $i++) {
            $str = substr($string, $i, 1);
            if (!eregi('([a-zA-Z0-9_/]+)', trim($str))) return false;
        }
        return true;
    }

    /* Проверка на наличие указанного урл, если нет - вычисление параметров */
    function _DetectURL($rpath)
    {
        global $mysql;
        if ($rpath == '/index/') $rpath = '/';
        $_SESSION['HTML_URL'] = $this->PrepareURL($rpath, false);
        $this->tpl->assign('_URL_', $_SESSION['HTML_URL']);
        $cnt = $mysql->sql("SELECT count(*) cnt FROM " . PREFIX . "pages WHERE page_path = '" . $rpath . "' AND site_id = " . $_SESSION['CMS_SITE_ID'], 1);
        if ($cnt['CNT'] == 1) return true;
        $path = ereg_replace('/$|^/', '', $rpath);
        $part = split('/', $path);
        for ($i = count($part) - 1; $i >= 0; $i--) {
            $check = '/' . $this->get_url_part($path, ($i + 1), true) . '/';
            $cnt = $mysql->sql("SELECT count(*) cnt FROM " . PREFIX . "pages WHERE page_path = '" . $check . "'", 1);
            if ($cnt['CNT'] == 0 && $i == 0) return false; //page not found
            // cool!!! page was found!!!
            if ($cnt['CNT'] == 1) {
                // reset url
                $_REQUEST['url'] = ereg_replace('^/', '', $check);
                return true;
            }
        }
    }

    /* Генерация списка страниц */
    function PageGenerator($page, $max_on_page, $t_recs, $prevurl = "", $url = "")
    {
        global $thref, $CMS_LANG;
        // за один заход не больше показывать
        $limit = 5;
        // Точки
        $right_point = false;
        $left_point = false;
        // Общее кол-во страниц
        $thref = intval($t_recs / $max_on_page);
        if ($thref == 0) $thref = 1;
        $check1 = $thref * $max_on_page;
        if ($check1 < $t_recs) $thref++;
        // Если только одна, выходим
        if ($thref == 1) return;
        // Определяем след. и пред. страницы
        $prev = $page - 1;
        $next = $page + 1;
        if ($prev <= 1) $prev = 1;
        if ($next >= $thref) $next = $thref;
        // Смотрим с чего бы начать
        // Если общее кол-во меньше либо равно лимиту
        if ($thref <= $limit) {
            $qwert = 1;
            $t_href = $thref;
        } // В противном случае начинается высшая математика
        else {
            // если текущая страница меньше либо равна лимиту
            if ($page <= $limit) {
                $qwert = 1;
                $t_href = $limit + $page;
                $right_point = true;
            } else {
                $qwert = $page - $limit;
                $t_href = $limit + $page;
                $right_point = true;
                if (($limit - $page) < -1) {
                    $left_point = true;
                }
                if ($t_href >= $thref) {
                    $t_href = $thref;
                    $right_point = false;
                }
            }
        }
        $ret = '
			 <table cellpadding="3" cellspacing="0" border="0" class="page_text">
			 <tr><td>' . $CMS_LANG['PAGER_1'] . ':</td></tr>
			 <tr><td><img src="' . $_SERVER['CMS_ROOT_URL'] . 'images/mks/arrow_left.gif" border="0" alt="' . $CMS_LANG['PAGER_3'] . '" align="absmiddle"/>&nbsp;<a href="' . $prevurl . '?' . ($url ? $url . '&' : '') . 'pg=' . $prev . '" class="page_1">' . $CMS_LANG['PAGER_3'] . '</a>&nbsp;&nbsp;<a href="' . $prevurl . '?' . ($url ? $url . '&' : '') . 'pg=' . $next . '" class="page_1">' . $CMS_LANG['PAGER_2'] . '</a>&nbsp;<img src="' . $_SERVER['CMS_ROOT_URL'] . 'images/mks/arrow_right.gif" border="0" alt="' . $CMS_LANG['PAGER_2'] . '" align="absmiddle"/></td></tr>
			 <tr><td>';
        if ($left_point) {
            $ret .= '<FONT class="page_normal"><a href="' . $prevurl . '?' . ($url ? $url . '&' : '') . 'pg=' . ($qwert - 1) . '" class="page_link"><</a></FONT>';
        }
        while ($qwert <= $t_href) {
            if ($qwert == $page) {
                $ret .= '<FONT class="page_active">' . $qwert . '</FONT>&nbsp;&nbsp;';
            } else {
                $ret .= '<FONT class="page_normal"><a href="' . $prevurl . '?' . ($url ? $url . '&' : '') . 'pg=' . $qwert . '" class="page_link">' . $qwert . '</a></FONT>&nbsp;&nbsp;';
            }
            $qwert++;
        }
        if ($right_point) {
            $ret .= '<FONT class="page_normal"><a href="' . $prevurl . '?' . ($url ? $url . '&' : '') . 'pg=' . $qwert . '" class="page_link">></a></FONT>';
        }
        $ret .= '</td></tr></table>';
        return $ret;
    }

    /*
        Форматирование текущего урла в нужный формат
        $url = адрес страницы
        $ext = true | false - добавлять в конце расширение файла
    */
    function PrepareURL($url, $ext = true)
    {
        $PregPath = str_replace('/', '-', $url);
        $PregPath = ereg_replace('^-', '/', $PregPath);
        $PregPath = ereg_replace('-$', '', $PregPath);
        if ($PregPath == '/' || $PregPath == '') $PregPath = '/index';
        $PregPath = ereg_replace('^/', '', $PregPath);
        return $_SERVER['CMS_ROOT_URL'] . HTMLLANG . $PregPath . ($ext ? '.html' : '');
    }

    /*
        Определение прав доступа к странице
        $rid = record_id в таблице cms_pages_content
    */
    function PageAccess($rid = 0)
    {
        global $mysql;
        // if ROOT admin is logged
        if ($_SESSION['USER_GROUP'] == -1) return true;
        $res = $mysql->sql("SELECT count(*) cnt FROM " . PREFIX . "pages_access WHERE page_id = " . $rid . " AND group_id = " . $_SESSION['USER_GROUP'], 1);
        if ($res['CNT'] == 0) return true;
        return false;
    }

    /*
        Обработчик заголовков модулей
     */
    function HeaderHandler()
    {
    }
}

/* Smarty dynamic function */
function smarty_block_dynamic($param, $content, &$smarty)
{
    return $content;
}

?>