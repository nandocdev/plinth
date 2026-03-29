<?php

declare(strict_types=1);

namespace App\Central\AuthenticationModule\DTOs;

use Illuminate\Http\Request;

final readonly class SystemAdminLoginData {
   public function __construct(
      public string $email,
      public string $password,
   ) {
   }

   public static function fromRequest(Request $request): self {
      return new self(
         email: (string) $request->input('email', ''),
         password: (string) $request->input('password', ''),
      );
   }
}
