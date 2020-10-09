<?php

namespace Sendportal\Base\Http\Controllers\Subscribers;

use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Reader\Exception\ReaderNotOpenedException;
use Exception;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use Rap2hpoutre\FastExcel\FastExcel;
use Sendportal\Base\Http\Controllers\Controller;
use Sendportal\Base\Http\Requests\SubscribersImportRequest;
use Sendportal\Base\Repositories\SegmentTenantRepository;
use Sendportal\Base\Services\Subscribers\ImportSubscriberService;

class SubscribersImportController extends Controller
{
    /** @var ImportSubscriberService */
    protected $subscriberService;

    public function __construct(ImportSubscriberService $subscriberService)
    {
        $this->subscriberService = $subscriberService;
    }

    /**
     * @throws Exception
     */
    public function show(SegmentTenantRepository $segmentRepo): ViewContract
    {
        $segments = $segmentRepo->pluck(auth()->user()->currentWorkspace()->id, 'name', 'id');

        return view('sendportal::subscribers.import', compact('segments'));
    }

    /**
     * @throws IOException
     * @throws UnsupportedTypeException
     * @throws ReaderNotOpenedException
     */
    public function store(SubscribersImportRequest $request): RedirectResponse
    {
        if ($request->file('file')->isValid()) {
            $filename = Str::random(16) . '.csv';

            $path = $request->file('file')->storeAs('imports', $filename, 'local');

            $errors = $this->validateCsvContents(Storage::disk('local')->path($path));

            if (count($errors->getBags())) {
                Storage::disk('local')->delete($path);

                return redirect()->back()
                    ->withInput()
                    ->with('error', __('The provided file contains errors'))
                    ->with('errors', $errors);
            }

            $counter = [
                'created' => 0,
                'updated' => 0
            ];

            (new FastExcel)->import(Storage::disk('local')->path($path), function (array $line) use ($request, &$counter) {
                $data = Arr::only($line, ['id', 'email', 'first_name', 'last_name']);

                $data['segments'] = $request->get('segments') ?? [];

                $subscriber = $this->subscriberService->import(auth()->user()->currentWorkspace()->id, $data);

                if ($subscriber->wasRecentlyCreated) {
                    $counter['created']++;
                } else {
                    $counter['updated']++;
                }
            });

            Storage::disk('local')->delete($path);

            return redirect()->route('sendportal.subscribers.index')
                ->with('success', __('Imported :created subscriber(s) and updated :updated subscriber(s) out of :total', [
                    'created' => $counter['created'],
                    'updated' => $counter['updated'],
                    'total' => $counter['created'] + $counter['updated']
                ]));
        }

        return redirect()->route('sendportal.subscribers.index')
            ->with('errors', __('The uploaded file is not valid'));
    }

    /**
     * @param string $path
     * @return ViewErrorBag
     * @throws IOException
     * @throws ReaderNotOpenedException
     * @throws UnsupportedTypeException
     */
    protected function validateCsvContents(string $path): ViewErrorBag
    {
        $errors = new ViewErrorBag();

        $row = 1;

        (new FastExcel)->import($path, function (array $line) use ($errors, &$row) {
            $data = Arr::only($line, ['id', 'email', 'first_name', 'last_name']);

            try {
                $this->validateData($data);
            } catch (ValidationException $e) {
                $errors->put('Row ' . $row, $e->validator->errors());
            }

            $row++;
        });

        return $errors;
    }

    /**
     * @param array $data
     * @throws ValidationException
     */
    protected function validateData(array $data): void
    {
        $validator = Validator::make($data, [
            'id' => 'integer',
            'email' => 'required|email:filter',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
