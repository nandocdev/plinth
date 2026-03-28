declare(strict_types=1);

namespace App\Central\AuthenticationModule\Actions;

use App\Central\AuthenticationModule\Models\User;
use App\Shared\Support\PasswordValidationRules;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and reset the user's forgotten password.
     *
     * @param  array<string, string>  $input
     */
    public function reset(User $user, array $input): void
    {
        Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->validate();

        $user->forceFill([
            'password' => $input['password'],
        ])->save();
    }

    /**
     * [RIESGOS]
     * - Cambio de contraseña que puede invalidar sesiones existentes si no se maneja logout de otros dispositivos.
     */
}
