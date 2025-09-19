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

namespace MpSoft\MpMassImport\Traits\FieldsList;

trait ImportFeatureProduct
{
    public function importFeatureProducts()
    {
        $boxes = $this->boxes;
        foreach ($boxes as $box) {
            $product = new \ModelImportFeatureProduct($box);
            if (!\Validate::isLoadedObject($product)) {
                $this->errors[] = sprintf(
                    $this->module->l('Prodotto %d non trovato', $this->name),
                    $this->strong($box)
                );

                continue;
            }

            $features = $product->json;
            $feature_list = [];
            foreach ($features as $key => $value) {
                if (in_array($key, ['id_product', 'reference', 'name'])) {
                    continue;
                }
                if ($feature = $this->isFeature($key)) {
                    if ($feature_values = $this->isFeature($value)) {
                        foreach ($feature_values as $feature_value) {
                            $feature[$key]['feature_values'][key($feature_values)] = array_shift($feature_values);
                        }
                    }
                    $feature_list[$key] = array_shift($feature);
                }
            }
            $feature_list = $this->getMissingFeaturesIds($product, $feature_list);
            $feature_list = $this->convertToFeatureStatic($feature_list);
            $product = new \Product($box);
            $product->deleteFeatures();
            foreach ($feature_list as $feature) {
                if ((!$feature['remove_feature'] || !$feature['remove_value']) && ($feature['id_feature'] != 0 && $feature['id_feature_value'] != 0)) {
                    $product->addFeaturesToDB($feature['id_feature'], $feature['id_feature_value']);
                }
            }
            if (\ModelImportFeatureProduct::deleteRow($box)) {
                $this->confirmations[] = $this->paragraph(
                    sprintf(
                        $this->module->l('Prodotto %s %s aggiornato.', $this->name),
                        $this->strong($product->reference),
                        $this->strong($product->name[$this->id_lang])
                    )
                );
            }
        }
    }

    protected function getFeaturesStatic($id_product)
    {
        $qry = 'SELECT fp.id_feature, fp.id_product, fp.id_feature_value, custom'
                . ' FROM `' . _DB_PREFIX_ . 'feature_product` fp'
                . ' LEFT JOIN `' . _DB_PREFIX_ . 'feature_value` fv ON (fp.id_feature_value = fv.id_feature_value)'
                . ' WHERE `id_product` = ' . (int) $id_product
                . ' ORDER BY fp.id_feature, fp.id_feature_value';
        $rows = \Db::getInstance()->executeS($qry);

        return $rows;
    }

    protected function convertToFeatureStatic($feature_list)
    {
        $rows = [];
        foreach ($feature_list as $key => $value) {
            $row = [
                'remove_feature' => $value['remove'],
                'remove_value' => true,
                'id_feature' => $value['id'],
                'id_feature_value' => 0,
            ];
            if (isset($value['feature_values'])) {
                foreach ($value['feature_values'] as $feature_value) {
                    $row['remove_value'] = $feature_value['remove'];
                    $row['id_feature_value'] = $feature_value['id'];
                    $rows[] = $row;
                }
            }
        }

        return $rows;
    }

    protected function isFeature($key)
    {
        return $this->matchPattern($key);
    }

    protected function getMissingFeaturesIds($product, $feature_list)
    {
        foreach ($feature_list as $key => &$feature) {
            if ($feature['value'] == '--') {
                continue;
            }
            if ($feature['remove'] && $feature['value'] != '--') {
                $this->warnings[] = sprintf(
                    $this->module->l('Rimossa caratteristica %s dal prodotto %s', $this->name),
                    $this->strong($feature['value']),
                    $this->strong($product->reference . '-' . $product->name)
                );

                continue;
            }
            if ((int) $feature['id'] == 0 && !$feature['value']) {
                $this->errors[] = sprintf(
                    $this->module->l('Caratteristica non trovata %s. Controlla il foglio Excel', $this->name),
                    $this->strong($key)
                );
                unset ($feature_list[$key]);

                continue;
            }
            if ((int) $feature['id'] == 0) {
                $id = $this->getFeatureByName($feature['value']);
                if ($id) {
                    $feature['id'] = $id;
                    $this->confirmations[] = $this->paragraph(
                        sprintf(
                            $this->module->l('Caratteristica %s trovata. Inserito il codice %s', $this->name),
                            $this->strong($feature['value']),
                            $this->strong($id)
                        )
                    );
                } else {
                    $this->errors[] = sprintf(
                        $this->module->l('Caratteristica non trovata %s. Controlla il foglio Excel', $this->name),
                        $this->strong($feature['value'])
                    );
                    unset ($feature_list[$key]);

                    continue;
                }
            }

            if (!isset($feature['feature_values'])) {
                continue;
            }

            foreach ($feature['feature_values'] as $key_fv => &$feature_values) {
                if ($feature_values['value'] == '--') {
                    continue;
                }
                if ($feature_values['remove'] && $feature_values['value'] != '--') {
                    $this->warnings[] = sprintf(
                        $this->module->l('Rimossa valore caratteristica %s di %s dal prodotto %s', $this->name),
                        $this->strong($feature_values['value']),
                        $this->strong($feature['value']),
                        $this->strong($product->reference . '-' . $product->name)
                    );

                    continue;
                }
                if ((int) $feature_values['id'] == 0 && !$feature_values['value']) {
                    $this->errors[] = sprintf(
                        $this->module->l('Valore Caratteristica non trovata %s - %s. Controlla il foglio Excel', $this->name),
                        $this->strong($feature['value']),
                        $this->strong($key_fv)
                    );
                    unset ($feature['feature_values'][$key]);

                    continue;
                }
                if ((int) $feature_values['id'] == 0) {
                    $id = $this->getFeatureValueByName((int) $feature['id'], $feature_values['value']);
                    if ($id) {
                        $feature_values['id'] = $id;
                        $this->confirmations[] = $this->paragraph(
                            sprintf(
                                $this->module->l('Valore caratteristica %s trovata. Inserito il codice %s;', $this->name),
                                $this->strong($feature_values['value']),
                                $this->strong($id)
                            )
                        );
                    } else {
                        $this->errors[] = sprintf(
                            $this->module->l('Valore Caratteristica non trovata %s - %s. Controlla il foglio Excel', $this->name),
                            $this->strong($feature['value']),
                            $this->strong($feature_values['value'])
                        );
                        unset ($feature['$feature_values'][$key]);

                        continue;
                    }
                }
            }
        }

        return $feature_list;
    }

    protected function paragraph($value)
    {
        return "<p>{$value}</p>";
    }

    protected function strong($value)
    {
        return "<strong>{$value}</strong>";
    }

    protected function getFeatureByName($feature_name)
    {
        $db = \Db::getInstance();
        $sql = new \DbQuery();
        $sql->select('id_feature')
            ->from('feature_lang')
            ->where('name = \'' . pSQL($feature_name) . '\'')
            ->where('id_lang=' . (int) $this->id_lang);

        return (int) $db->getValue($sql);
    }

    protected function getFeatureValueByName($id_feature, $feature_value)
    {
        $db = \Db::getInstance();
        $sql = new \DbQuery();
        $sql->select('a.id_feature_value')
            ->from('feature_value_lang', 'a')
            ->innerJoin('feature_value', 'fv', 'fv.id_feature=' . (int) $id_feature)
            ->where('value = \'' . pSQL($feature_value) . '\'')
            ->where('id_lang=' . (int) $this->id_lang);

        return (int) $db->getValue($sql);
    }

    protected function matchPattern($value)
    {
        $values = explode(',', $value);
        $matches = [];
        $return = [];

        foreach ($values as $value) {
            if (!trim($value)) {
                return false;
            } elseif (preg_match('/^(--)$/i', $value, $matches)) {
                return false;
            } elseif (preg_match('/^(--)(\d+)(::)(.*)$/i', $value, $matches)) {
                $return[$value] = [
                    'remove' => true,
                    'id' => (int) $matches[2],
                    'value' => $matches[4],
                ];
            } elseif (preg_match('/^(\d+)(::)(.*)$/i', $value, $matches)) {
                $return[$value] = [
                    'remove' => false,
                    'id' => (int) $matches[1],
                    'value' => $matches[3],
                ];
            } elseif (preg_match('/^(::)(.*)$/i', $value, $matches)) {
                $return[$value] = [
                    'remove' => false,
                    'id' => 0,
                    'value' => $matches[2],
                ];
            } else {
                $return[$value] = [
                    'remove' => false,
                    'id' => 0,
                    'value' => trim($value),
                ];
            }
        }

        return $return;
    }

    public function getFieldsListExportFeatureProduct()
    {
        $categories = $this->cookieGetValue('HCA_CATEGORY_TREE');
        $search_default = $this->cookieGetValue('HCA_SELECT_IN_DEFAULT_CATEGORY');
        $search_assoc = $this->cookieGetValue('HCA_SELECT_IN_ASSOCIATED_CATEGORIES');
        $id_lang = (int) \Context::getContext()->language->id;
        $module = $this->module;
        $name = $this->name;

        return [
            'id_product' => [
                'title' => $module->l('Id', $name),
                'type' => 'text',
                'size' => 64,
                'align' => 'text-right',
                'search' => true,
                'filter_key' => 'a!id_product',
            ],
            'image' => [
                'title' => $module->l('Image', $name),
                'align' => 'center',
                'image' => 'p',
                'orderby' => false,
                'filter' => false,
                'search' => false,
            ],
            'reference' => [
                'title' => $module->l('Reference', $name),
                'type' => 'text',
                'float' => true,
                'size' => 'auto',
                'search' => true,
                'filter_key' => 'a!reference',
            ],
            'category_default' => [
                'title' => $module->l('Default Category', $name),
                'type' => 'text',
                'float' => true,
                'size' => 'auto',
                'filter_key' => 'cat!name',
            ],
            'name' => [
                'title' => $module->l('Name', $name),
                'type' => 'text',
                'float' => true,
                'size' => 'auto',
                'search' => true,
                'filter_key' => 'b!name',
            ],
        ];
    }

    public function getFieldsListImportFeatureProduct()
    {
        $id_lang = (int) \Context::getContext()->language->id;
        $module = $this->module;
        $name = $this->name;
        $this->addRowAction('toProduct');
        $this->bulk_actions = [
            'import' => [
                'text' => $this->module->l('Aggiorna i prodotti selezionati', $this->name),
                'confirm' => $this->module->l('Aggiornare i prodotti selezionati?', $this->name),
                'icon' => 'icon-download',
            ],
            'import_all' => [
                'text' => $this->module->l('Aggiorna tutti i prodotti del Foglio Excel', $this->name),
                'confirm' => $this->module->l('Aggiornare tutti i prodotti?', $this->name),
                'icon' => 'icon-download text-info',
            ],
            'divider000' => [
                'text' => 'divider',
            ],
            'delete' => [
                'text' => $this->module->l('Elimina dalla lista', $this->name),
                'confirm' => $this->module->l('Eliminare i prodotti selezionati?', $this->name),
                'icon' => 'icon-trash text-danger',
            ],
            'divider001' => [
                'text' => 'divider',
            ],
        ];

        return [
            'id_product' => [
                'title' => $module->l('Id', $name),
                'type' => 'text',
                'size' => 64,
                'align' => 'text-right',
                'search' => true,
                'filter_key' => 'a!id_product',
                'class' => 'fixed-width-sm',
            ],
            'image' => [
                'title' => $module->l('Image', $name),
                'align' => 'center',
                'image' => 'p',
                'orderby' => false,
                'filter' => false,
                'search' => false,
            ],
            'reference' => [
                'title' => $module->l('Reference', $name),
                'type' => 'text',
                'float' => true,
                'size' => 'auto',
                'search' => true,
                'filter_key' => 'a!reference',
            ],
            'category_default' => [
                'title' => $module->l('Default Category', $name),
                'type' => 'text',
                'float' => true,
                'size' => 'auto',
                'filter_key' => 'cat!name',
            ],
            'name' => [
                'title' => $module->l('Name', $name),
                'type' => 'text',
                'float' => true,
                'size' => 'auto',
                'search' => true,
                'filter_key' => 'b!name',
            ],
            'active' => [
                'title' => $this->l('Active'),
                'active' => 'active',
                'filter_key' => 'b!active',
                'align' => 'text-center',
                'type' => 'bool',
                'class' => 'fixed-width-sm',
            ],
        ];
    }
}
