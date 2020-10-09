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

            $errors = new ViewErrorBag();

            $row = 1;

            $subscribers = (new FastExcel)->import(Storage::disk('local')->path($path), function (array $line) use ($request, $errors, &$row) {
                try {
                    $data = Arr::only($line, ['id', 'email', 'first_name', 'last_name']);

                    $this->validateData($data);

                    $data['segments'] = $request->get('segments') ?? [];

                    $row++;

                    return $this->subscriberService->import(auth()->user()->currentWorkspace()->id, $data);
                } catch (ValidationException $e) {
                    $errors->put('Row ' . $row, $e->validator->errors());

                    $row++;
                }

                return null;
            });

            Storage::disk('local')->delete($path);

            if (empty($errors->getBags())) {
                return redirect()->route('sendportal.subscribers.index')
                    ->with('success', __('Imported :count subscriber(s)', ['count' => $subscribers->count()]));
            }

            return redirect()->back()
                ->with('errors', $errors)
                ->with('warning', __('Imported :count subscriber(s) out of :total', [
                    'count' => $subscribers->count(),
                    'total' => $row
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
