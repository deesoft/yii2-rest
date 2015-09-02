<?php

namespace dee\rest;

use yii\filters\auth\AuthMethod;

/**
 * Description of GuestAuth
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class GuestAuth extends AuthMethod
{

    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response)
    {
        return false;
    }
}