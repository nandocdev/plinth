<?php

declare(strict_types=1);

namespace App\Central\AuthenticationModule\Actions;

use App\Central\AuthenticationModule\DTOs\RegisterSystemAdminData;
use App\Central\AuthenticationModule\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

final class CreateNewUser implements CreatesNewUsers {
    /** @param array<string, mixed> $input */
    public function create(array $input): User {
        Validator::make($input, RegisterSystemAdminData::rules())->validate();

        $data = RegisterSystemAdminData::fromArray($input);

        return app(RegisterSystemAdminAction::class)->execute($data);
    }
}
