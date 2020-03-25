<?php

namespace Sendportal\Base\Interfaces;

use Sendportal\Base\Models\Message;
use Carbon\Carbon;

interface EmailWebhookServiceInterface
{
    /**
     * @param $messageId
     * @param Carbon $timestamp
     * @param $link
     * @return mixed
     */
    public function handleClick($messageId, Carbon $timestamp, $link);

    /**
     * @param $messageId
     * @param Carbon $timestamp
     * @param $ipAddress
     */
    public function handleOpen($messageId, Carbon $timestamp, $ipAddress);

    /**
     * @param $messageId
     * @param Carbon $timestamp
     */
    public function handleDelivery($messageId, Carbon $timestamp);

    /**
     * @param string $messageId
     * @param Carbon $timestamp
     */
    public function handleComplaint($messageId, $timestamp);

    /**
     * @param $messageId
     * @param Carbon $timestamp
     */
    public function handlePermanentBounce($messageId, $timestamp);

    /**
     * @param $messageId
     * @param $severity
     * @param $description
     * @param $timestamp
     * @return mixed
     */
    public function handleFailure($messageId, $severity, $description, $timestamp);
}
