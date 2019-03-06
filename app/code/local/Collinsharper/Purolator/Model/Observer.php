<?php
/**
 * Collinsharper Purolator Module
 *
 * PHP version 5
 *
 * @category Shipping_Modules
 * @package  Collinsharper.Purolator.Model
 * @author   Collins Harper  <ch@collinsharper.com>
 * @license  http://collinsharper.com Proprietary License
 * @link     http://collinsharper.com
 */

/**
 * Collinsharper_Purolator_Model_Observer
 *
 * @category Shipping_Modules
 * @package  Collinsharper.Purolator.Model
 * @author   Collins Harper  <ch@collinsharper.com>
 * @license  http://collinsharper.com Proprietary License
 * @link     http://collinsharper.com
 */
class Collinsharper_Purolator_Model_Observer
{

    /**
     * Function comment
     *
     * @param type $observer event observer object
     *
     * @return type $collection Collinsharper_Purolator_Model_Observer
     *
     */
    public function prepareShipmentGrid($observer)
    {
        $collection = $observer->getCollection();

        $arr = array('shipment_increment_id' => 'increment_id');

        $collection->getSelect()->columns($arr);

        $res = Mage::getSingleton('core/resource');
        $param = array();
        $param[0]=array('cs' => $res->getTableName('ch_purolator_shipment'));
        $param[1]='main_table.entity_id = cs.magento_shipment_id';
        $param[2]=array('shipment_pin');

        $collection->getSelect()->joinLeft($param[0], $param[1], $param[2]);
        $action = $observer->getAction();

        if ($action == "create") {
            $param = array();
            $param[0] = 'cs.shipment_pin';
            $param[1]=array('null' => true);
            $collection->addFieldToFilter($param[0], $param[1]);
        }

        if ($action == "index") {
            $param = array();
            $param[0]='cs.shipment_pin';
            $param[1]=array('notnull' => true);
            $collection->addFieldToFilter($param[0], $param[1]);
        }

        $salesFlatOrder = $res->getTableName('sales_flat_order');
        $param = array();
        $param[0]= 'main_table.order_id = o.entity_id';
        $param[1]='CONCAT(o.customer_firstname, \' \',o.customer_lastname)';
        $collection->getSelect()->joinLeft(
            array('o' => $salesFlatOrder), $param[0], array(
                'order_increment_id' => 'o.increment_id',
                'order_created_at' => 'o.created_at',
                'ordered_by' => $param[1],
            )
        );
        // Add purolator filter
        $param = array();
        $param[0]='o.shipping_description';
        $param[1]=array('like' => 'Purolator%');
        $collection->addFieldToFilter($param[0], $param[1]);

        return $collection;
    }

    /**
     * Clear Cache
     *
     * @param $observer Observer Object
     *
     * @return void
     */
    public function clearCache($observer)
    {
        $cw = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = 'truncate table ' . Mage::getSingleton('core/resource')->getTableName('chpurolatormodule_cache') . ';';
        $cw->query($sql);
    }
}
