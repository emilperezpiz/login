<?php

namespace App\Controller\API\v1\Amappzing\Interfaces;

interface IUserSetting
{
    public function show($context): array;

    public function save($context, $parameters): array;
}
