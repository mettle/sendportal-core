<?php

declare(strict_types=1);

namespace Sendportal\Base\Rules;

use Illuminate\Contracts\Validation\Rule;
use Sendportal\Base\Traits\ChecksInvitations;

class ValidInvitation implements Rule
{
    use ChecksInvitations;

    /**
     * @inheritDoc
     */
    public function passes($attribute, $value)
    {
        if (empty($value)) {
            return true;
        }

        return $this->isValidInvitation($value);
    }

    /**
     * @inheritDoc
     */
    public function message()
    {
        return __('The invitation is no longer valid.');
    }
}
