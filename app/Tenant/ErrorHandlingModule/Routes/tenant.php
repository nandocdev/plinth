<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::fallback(static function () {
   abort(404);
});
