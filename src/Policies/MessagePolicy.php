<?php

declare(strict_types=1);

namespace Sendportal\Base\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Models\User;

class MessagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Message $message
     * @return Response
     */
    public function delete(User $user, Message $message)
    {
        return is_null($message->sent_at)
            ? Response::allow()
            : Response::deny(__('A sent message cannot be deleted'));
    }
}
