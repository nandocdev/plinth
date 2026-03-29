<?php

declare(strict_types=1);

namespace App\Central\AuthenticationModule\DTOs;

final readonly class RegisterSystemAdminData {
   public function __construct(
      public string $name,
      public string $email,
      public string $password,
   ) {
   }

   /** @param array<string, mixed> $input */
   public static function fromArray(array $input): self {
      return new self(
         name: (string) $input['name'],
         email: (string) $input['email'],
         password: (string) $input['password'],
      );
   }

   /** @return array<string, array<int, string>> */
   public static function rules(): array {
      return [
         'name' => ['required', 'string', 'max:255'],
         'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
         'password' => ['required', 'string', 'min:8', 'confirmed'],
      ];
   }
}
