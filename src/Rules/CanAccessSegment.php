<?php

declare(strict_types=1);

namespace Sendportal\Base\Rules;

use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Models\Segment;
use Illuminate\Contracts\Validation\Rule;

class CanAccessSegment implements Rule
{

    public function passes($attribute, $value): bool
    {
        $segment = Segment::find($value);

        if (!$segment) {
            return false;
        }

        return $segment->workspace_id == Sendportal::currentWorkspaceId();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'Segment ID :input does not exist.';
    }
}
