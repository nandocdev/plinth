declare(strict_types=1);

namespace App\Central\AuthenticationModule\Actions;

use App\Central\AuthenticationModule\Models\User;
use App\Shared\Support\PasswordValidationRules;
use App\Shared\Support\ProfileValidationRules;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
        ]);
    }

    /**
     * [RIESGOS]
     * - Registro de usuario central sin vinculación a Tenant en este paso.
     * - El password se guarda plano en el array (Eloquent se encarga del hash según el modelo).
     */
}
