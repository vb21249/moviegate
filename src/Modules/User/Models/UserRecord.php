<?php

declare(strict_types=1);

namespace App\Modules\User\Models;

use App\Common\Models\BaseActiveRecord;
use yii\web\IdentityInterface;

/**
 * ActiveRecord for users.
 *
 * @property int|string $id
 * @property string $email
 * @property string $username
 * @property string $password_hash
 * @property string|null $avatar_url
 * @property string|null $bio
 * @property string $status
 * @property string|null $email_verified_at
 * @property string|null $last_login_at
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
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
