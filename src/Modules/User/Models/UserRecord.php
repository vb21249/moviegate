<?php

declare(strict_types=1);

namespace App\Modules\User\Models;

use App\Common\Models\BaseActiveRecord;
use yii\web\IdentityInterface;

/**
 * ActiveRecord for users.
 *
 * @property int|string $id
 */
final class UserRecord extends BaseActiveRecord implements IdentityInterface
{
    public static function tableName(): string
    {
        return '{{%users}}';
    }

    public static function findIdentity($id): ?IdentityInterface
    {
        $identity = static::findOne($id);

        return $identity instanceof IdentityInterface ? $identity : null;
    }

    public static function findIdentityByAccessToken($token, $type = null): ?IdentityInterface
    {
        return null;
    }

    public function getId(): int|string
    {
        return $this->id;
    }

    public function getAuthKey(): ?string
    {
        return null;
    }

    public function validateAuthKey($authKey): bool
    {
        return false;
    }
}
