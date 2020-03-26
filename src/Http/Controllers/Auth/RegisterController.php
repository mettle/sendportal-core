<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Auth;

use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Models\Workspace;
use Sendportal\Base\Models\User;
use Sendportal\Base\Rules\ValidInvitation;
use Sendportal\Base\Services\Workspaces\AcceptInvitation;
use Sendportal\Base\Services\Workspaces\CreateWorkspace;
use Sendportal\Base\Traits\ChecksInvitations;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers,
        ChecksInvitations;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/campaigns';

    /** @var AcceptInvitation */
    private $acceptInvitation;

    /** @var CreateWorkspace */
    private $createWorkspace;

    public function __construct(AcceptInvitation $acceptInvitation, CreateWorkspace $createWorkspace)
    {
        $this->middleware('guest');

        $this->acceptInvitation = $acceptInvitation;
        $this->createWorkspace = $createWorkspace;
    }

    /**
     * Show the application registration form.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function showRegistrationForm(Request $request)
    {
        $invitation = $request->get('invitation');

        if ($invitation && $this->isInvalidInvitation($invitation)) {
            return redirect('register')
                ->with('error', __('The invitation is no longer valid.'));
        }

        return view('sendportal::auth.register');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     *
     * @return ValidatorContract
     */
    protected function validator(array $data): ValidatorContract
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'/*, 'confirmed'*/],
            'invitation' => [new ValidInvitation()]
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     *
     * @return User
     */
    protected function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'api_token' => Str::random(80),
            ]);

            if ($token = request('invitation')) {
                // Attach user to invited workspace.
                $this->acceptInvitation->handle($user, $this->getInvitationFromToken($token));
            } else {
                // Create a new workspace and attach as owner.
                $this->createWorkspace->handle($user, $data['company_name'], Workspace::ROLE_OWNER);
            }

            return $user;
        });
    }
}
