<?php

declare(strict_types=1);

namespace Sendportal\Base\Traits;

use Sendportal\Base\Models\Invitation;

trait ChecksInvitations
{
    protected function isInvalidInvitation(string $invitationToken): bool
    {
        return !$this->isValidInvitation($invitationToken);
    }

    protected function isValidInvitation(string $invitationToken): bool
    {
        $invitation = $this->getInvitationFromToken($invitationToken);

        if (!$invitation) {
            return false;
        }

        return $invitation->isNotExpired();
    }

    protected function getInvitationFromToken(string $invitationToken): ?Invitation
    {
        return Invitation::where('token', $invitationToken)->first();
    }
}
