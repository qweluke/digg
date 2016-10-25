<?php

/**
 * Created by PhpStorm.
 * User: lmalicki
 * Date: 31.05.16
 * Time: 22:32
 */

namespace UserBundle\Handler;

Interface UserHandlerInterface
{
    public function login($username, $password);
}