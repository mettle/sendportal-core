<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Requests\Teams;

use Sendportal\Base\Models\Team;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class TeamInvitationStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->ownsCurrentTeam();
    }

    public function validator(): ValidatorContract
    {
        $validator = Validator::make($this->all(), [
            'email' => ['required', 'email', 'max:255'],
        ]);

        return $validator->after(function ($validator) {
            return $this->verifyEmailNotAlreadyOnTeam($validator, $this->user()->currentTeam)
                ->verifyEmailNotAlreadyInvited($validator, $this->user()->currentTeam);
        });
    }

    protected function verifyEmailNotAlreadyOnTeam(ValidatorContract $validator, Team $team): self
    {
        if ($team->users()->where('email', $this->email)->exists()) {
            $validator->errors()->add('email', __('That user is already on the team.'));
        }

        return $this;
    }

    protected function verifyEmailNotAlreadyInvited(ValidatorContract $validator, Team $team): self
    {
        if ($team->invitations()->where('email', $this->email)->exists()) {
            $validator->errors()->add('email', __('That user is already invited to the team.'));
        }

        return $this;
    }
}
