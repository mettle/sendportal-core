<?php

declare(strict_types=1);

namespace Sendportal\Base\Rules;

use Illuminate\Contracts\Validation\Rule;
use Sendportal\Base\Facades\Sendportal;
use Sendportal\Base\Models\Tag;

class CanAccessTag implements Rule
{
    public function passes($attribute, $value): bool
    {
        $tag = Tag::find($value);

        if (!$tag) {
            return false;
        }

        return $tag->workspace_id == Sendportal::currentWorkspaceId();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'Tag ID :input does not exist.';
    }
}
