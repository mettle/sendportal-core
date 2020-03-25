<?php

declare(strict_types=1);

namespace Sendportal\Base\Http\Controllers\Webview;

use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Models\Message;
use Sendportal\Base\Services\Content\MergeContent;
use Exception;
use Illuminate\Contracts\View\View as ViewContract;

class WebviewController extends Controller
{
    /** @var MergeContent */
    private $merger;

    public function __construct(MergeContent $merger)
    {
        $this->merger = $merger;
    }

    /**
     * @throws Exception
     */
    public function show(string $messageHash): ViewContract
    {
        /** @var Message $message */
        $message = Message::with('subscriber')->where('hash', $messageHash)->first();

        $content = $this->merger->handle($message);

        return view('webview.show', compact('content'));
    }
}
