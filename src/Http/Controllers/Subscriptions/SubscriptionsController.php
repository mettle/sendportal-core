<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Subscriptions;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\SubscriptionToggleRequest;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Models\UnsubscribeEventType;
use Sendportal\Base\Repositories\Messages\MessageTenantRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SubscriptionsController extends Controller
{
    /** @var MessageTenantRepositoryInterface */
    protected $messages;

    public function __construct(MessageTenantRepositoryInterface $messages)
    {
        $this->messages = $messages;
    }

    /**
     * Unsubscribe a subscriber.
     */
    public function unsubscribe(string $messageHash): View
    {
        $message = $this->resolveMessage($messageHash);

        return view('sendportal::subscriptions.unsubscribe', compact('message'));
    }

    /**
     * Unsubscribe a subscriber straight away (RFC 8058 one-click unsubscribe).
     *
     * Mailbox providers POST "List-Unsubscribe=One-Click" here from the List-Unsubscribe
     * header. The body is not checked: the message hash is what authorises the request,
     * as it does for the unsubscribe page.
     */
    public function oneClickUnsubscribe(string $messageHash): Response
    {
        $this->markUnsubscribed($this->resolveMessage($messageHash));

        return response(__('You have been successfully removed from the mailing list.'));
    }

    /**
     * Subscribe a subscriber.
     */
    public function subscribe(string $messageHash): View
    {
        $message = $this->resolveMessage($messageHash);

        return view('sendportal::subscriptions.subscribe', compact('message'));
    }

    /**
     * Toggle subscriber subscription state.
     */
    public function update(SubscriptionToggleRequest $request, string $messageHash): RedirectResponse
    {
        $message = $this->resolveMessage($messageHash);
        $subscriber = $message->subscriber;

        $unsubscribed = (bool)$request->get('unsubscribed');

        if ($unsubscribed) {
            $this->markUnsubscribed($message);

            return redirect()->route('sendportal.subscriptions.subscribe', $message->hash)
                ->with('success', __('You have been successfully removed from the mailing list.'));
        }

        $message->unsubscribed_at = null;
        $message->save();

        $subscriber->unsubscribed_at = null;
        $subscriber->unsubscribe_event_id = null;
        $subscriber->save();

        return redirect()->route('sendportal.subscriptions.unsubscribe', $message->hash)
            ->with('success', __('You have been added to the mailing list.'));
    }

    /**
     * Record that the subscriber unsubscribed themselves from this message.
     */
    protected function markUnsubscribed(Message $message): void
    {
        $message->unsubscribed_at = now();
        $message->save();

        $subscriber = $message->subscriber;
        $subscriber->unsubscribed_at = now();
        $subscriber->unsubscribe_event_id = UnsubscribeEventType::MANUAL_BY_SUBSCRIBER;
        $subscriber->save();
    }

    /**
     * Resolve the message behind a subscription link.
     *
     * An unknown hash, or a message whose subscriber has been deleted, is a 404
     * rather than a 500 - these links live in inboxes long after the underlying
     * records may have been pruned.
     */
    protected function resolveMessage(string $messageHash): Message
    {
        /** @var Message|null $message */
        $message = Message::with('subscriber')->where('hash', $messageHash)->first();

        if (! $message || ! $message->subscriber) {
            throw new NotFoundHttpException('Unable to resolve subscription for hash ' . $messageHash);
        }

        return $message;
    }
}
