<?php

declare(strict_types=1);

namespace App\Common\Repositories;

use yii\db\Connection;
use Yii;

/**
 * Base repository for DB access.
 */
abstract class BaseRepository
{
    /**
     * @return Connection
     */
    protected function db(): Connection
    {
        /** @var Connection $db */
        $db = Yii::$app->db;

        return $db;
    }
}
