<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Subscriptions;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\SubscriptionToggleRequest;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Models\UnsubscribeEventType;
use Sendportal\Base\Repositories\Messages\MessageTenantRepository;

class SubscriptionsController extends Controller
{
    /** @var MessageTenantRepository */
    protected $messages;

    public function __construct(MessageTenantRepository $messages)
    {
        $this->messages = $messages;
    }

    /**
     * Unsubscribe a subscriber.
     */
    public function unsubscribe(string $messageHash): View
    {
        $message = Message::with('subscriber')->where('hash', $messageHash)->first();

        return view('sendportal::subscriptions.unsubscribe', compact('message'));
    }

    /**
     * Subscribe a subscriber.
     */
    public function subscribe(string $messageHash): View
    {
        $message = Message::with('subscriber')->where('hash', $messageHash)->first();

        return view('sendportal::subscriptions.subscribe', compact('message'));
    }

    /**
     * Toggle subscriber subscription state.
     */
    public function update(SubscriptionToggleRequest $request, string $messageHash): RedirectResponse
    {
        $message = Message::where('hash', $messageHash)->first();
        $subscriber = $message->subscriber;

        $unsubscribed = (bool)$request->get('unsubscribed');

        if ($unsubscribed) {
            $message->unsubscribed_at = now();
            $message->save();

            $subscriber->unsubscribed_at = now();
            $subscriber->unsubscribe_event_id = UnsubscribeEventType::MANUAL_BY_SUBSCRIBER;
            $subscriber->save();

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
}
