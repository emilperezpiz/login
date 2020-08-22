<?php

namespace App\Controller\API\v1\Gym\Actions\Interfaces;

interface IUser
{
    public function list($context, string $identificador): array;

    public function new($context, string $identificador, array $parameters): array;

    public function show($context, string $identificador, string $currentId): array;

    public function edit($context, string $identificador, string $currentId, array $parameters): array;

    public function remove($context, string $identificador, string $currentId): array;
}