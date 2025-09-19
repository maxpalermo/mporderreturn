<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Massimiliano Palermo <maxx.palermo@gmail.com>
 * @copyright Since 2016 Massimiliano Palermo
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace MpSoft\MpMassImport\Traits;
trait SmartyTpl
{
    /**
     * Set Tpl Template and parameters
     *
     * @param string $template path of the template without .tpl extension
     * @param array $params array of parameters to pass to smarty compiler
     * @param bool $admin if true add admin folder to path
     *
     * @return string the fetched template
     */
    public function renderTplAdmin($template, $params = [])
    {
        return $this->renderTpl($template, $params, 'admin');
    }

    public function renderTplHook($template, $params = [])
    {
        return $this->renderTpl($template, $params, 'hook');
    }

    public function renderTplFront($template, $params = [])
    {
        return $this->renderTpl($template, $params, 'front');
    }

    public function renderTpl($template, $params = [], $folder = '')
    {
        $smarty = \Context::getContext()->smarty;
        if ($folder && !$this->endsWith($folder, '/')) {
            $folder .= '/';
        }
        $pathInfo = pathinfo($template);
        if (isset($pathInfo['dirname']) && $pathInfo['dirname']) {
            $tpl = $pathInfo['dirname'] . '/' . $pathInfo['filename'];
        } else {
            $tpl = $pathInfo['filename'];
        }

        if (is_a($this, 'Module')) {
            $path = $this->getLocalPath() . 'views/templates/';
        } else {
            $path = $this->module->getLocalPath() . 'views/templates/';
        }
        $template = $path . $folder . $tpl . '.tpl';
        if ($params) {
            $smarty->assign($params);
        }

        return $smarty->fetch($template);
    }

    protected function endsWith($string, $needle)
    {
        return strpos($string, $needle, -1);
    }
}
