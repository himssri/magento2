<?php
/**
 * Test framework custom connection adapter
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Db;

class ConnectionAdapter extends \Magento\Framework\Model\Resource\Type\Db\Pdo\Mysql
{
    /**
     * Retrieve DB connection class name
     *
     * @return string
     */
    protected function getDbConnectionClassName()
    {
        return \Magento\TestFramework\Db\Adapter\Mysql::class;
    }
}
