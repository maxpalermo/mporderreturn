<?php
/**
* 2007-2018 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    Massimiliano Palermo <info@mpsoft.it>
*  @copyright 2021 Massimiliano Palermo
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class ModelImportEan13_old extends ObjectModel
{
    /** @var int */
    public $id_product;

    /** @var string */
    public $reference;

    /** @var string */
    public $ean13;

    /** @var string|array */
    public $json;

    /** @var string */
    public $date_add;

    /** @var string */
    public $date_upd;

    /** @var int */
    protected $id_lang;

    /** @var int */
    protected $id_shop;

    /** @var string */
    protected $controllerName;

    public static $definition = [
        'table' => 'mp_massimport_ean13',
        'primary' => 'id_product_attribute',
        'multilang' => false,
        'fields' => [
            'id_product' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true,
            ],
            'reference' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isReference',
                'size' => 255,
                'required' => true,
            ],
            'ean13' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isEan13',
                'size' => 13,
                'required' => false,
            ],
            'json' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'text' => true,
                'required' => false,
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => true,
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => true,
            ],
        ],
    ];

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id);

        if ($this->id) {
            try {
                json_encode($this->json);
                if (!json_last_error()) {
                    $this->json = Tools::jsonDecode($this->json, true);
                }
            } catch (Exception $e) {
                $this->errors[] = $e->getMessage();
            }
        }
    }

    public function add($auto_date = true, $null_values = false)
    {
        if (is_array($this->json)) {
            $this->json = Tools::jsonEncode($this->json);
        }

        return parent::add($auto_date, $null_values);
    }

    public function update($null_values = false)
    {
        if (is_array($this->json)) {
            $this->json = Tools::jsonEncode($this->json);
        }

        return parent::update($null_values);
    }
}
