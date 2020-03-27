<?php

namespace Sendportal\Base\Presenters;

use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Exception;
use Illuminate\Support\Collection;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Repositories\Campaigns\CampaignTenantRepositoryInterface;
use Sendportal\Base\Repositories\Messages\MessageTenantRepositoryInterface;
use Sendportal\Base\Repositories\MessageUrlRepository;

class CampaignReportPresenter
{
    /** @var Campaign */
    protected $campaign;

    /** @var CampaignTenantRepositoryInterface */
    protected $campaignRepo;

    /** @var MessageTenantRepositoryInterface */
    protected $messageRepo;

    /** @var MessageUrlRepository */
    protected $messageUrlRepo;

    public function __construct(Campaign $campaign)
    {
        $this->campaignRepo = app(CampaignTenantRepositoryInterface::class);
        $this->messageRepo = app(MessageTenantRepositoryInterface::class);
        $this->messageUrlRepo = app(MessageUrlRepository::class);

        $this->campaign = $campaign;
    }

    /**
     * Generate the data for the view
     *
     * @return array
     * @throws Exception
     */
    public function generate()
    {
        if (! $this->campaign) {
            throw new Exception('Campaign must be initialised');
        }

        return [
            'chartData' => $this->getChartData(),
            'campaignUrls' => $this->getCampaignUrls(),
        ];
    }

    /**
     * Generate the chart data
     *
     * @throws Exception
     */
    protected function getChartData()
    {
        // get the boundaries of the first and last event from the database
        $boundaries = $this->messageRepo->getFirstLastOpenedAt(auth()->user()->currentWorkspace()->id, Campaign::class, $this->campaign->id);

        // extract Carbon instances for $first and $last
        list($first, $last) = $this->calculateFirstLast($boundaries);

        // calculate the timespan between the first and last even
        $timespan = $this->calculateTimespan($first, $last);

        // calculate the number of seconds for the given timespan
        $secondsPerInterval = $this->calculateSecondsInterval($timespan);

        // modify first so that it matches with the database intervals (i.e. using DIV in mysql)
        $first = Carbon::createFromTimestamp(floor($first->timestamp / $secondsPerInterval) * $secondsPerInterval);

        // create the php DateTime intervals
        $intervals = $this->createIntervals($first, $last, $timespan);

        // calculate the opens per period frm the database
        $opensPerPeriod = $this->messageRepo->countUniqueOpensPerPeriod(auth()->user()->currentWorkspace()->id, Campaign::class, $this->campaign->id, $secondsPerInterval);

        // merge in the actual opens to the intervals
        $periods = $this->populatePeriods($opensPerPeriod, $intervals);

        $result = [];

        // separate the periods into labels and data for chart.js
        foreach ($periods as $period) {
            $result['labels'][] = $period['opened_at'];
            $result['data'][] = $period['open_count'];
        }

        return $result;
    }

    /**
     * Create the DatePeriod intervals between the first and last opens
     *
     * @param Carbon $first
     * @param Carbon $last
     * @param int $timespan
     * @return DatePeriod
     * @throws Exception
     */
    protected function createIntervals(Carbon $first, Carbon $last, $timespan): DatePeriod
    {
        $interval = $this->calculateDateTimeInterval($timespan);

        return new DatePeriod(
            new DateTime($first),
            new DateInterval('PT' . $interval),
            new DateTime($last)
        );
    }

    /**
     * Calculate the number of seconds between the first and last event
     * rounded to the nearest timespan interval
     *
     * @param Carbon $first
     * @param Carbon $last
     * @return int
     */
    protected function calculateTimespan(Carbon $first, Carbon $last)
    {
        foreach ($this->getTimeSpanIntervals() as $timespan => $item) {
            if ($last->lt($first->copy()->addSeconds($timespan))) {
                return $timespan;
            }
        }

        return 2592000; // 30 days
    }

    /**
     * Calculate the first and last timestamps
     *
     * @param $boundaries
     * @return array
     */
    protected function calculateFirstLast($boundaries)
    {
        if (isset($boundaries->first)) {
            $first = Carbon::parse($boundaries->first);
            $last = Carbon::parse($boundaries->last);
        } else {
            $first = Carbon::parse($this->campaign->scheduled_at);
            $last = $first->copy()->addHours(12);
        }

        $first = $first->copy()->startOfHour();
        $last = $last->copy()->endOfHour();

        return [$first, $last];
    }

    /**
     * Calculate the DateTime interval for a given timespan
     *
     * @param int $timespan
     * @return mixed|string
     */
    protected function calculateDateTimeInterval($timespan)
    {
        $timespanIntervals = $this->getTimeSpanIntervals();

        if (isset($timespanIntervals[$timespan])) {
            return $timespanIntervals[$timespan]['interval'];
        }

        return '24H';
    }

    /**
     * Calculate the interval in seconds for a given timespan
     *
     * @param int $timespan
     * @return int|mixed
     */
    protected function calculateSecondsInterval($timespan)
    {
        $timespanIntervals = $this->getTimeSpanIntervals();

        if (isset($timespanIntervals[$timespan])) {
            return $timespanIntervals[$timespan]['seconds'];
        }

        return 86400;
    }

    /**
     * Map the timespan between the first and last event
     *
     * The array key is the timespan and the elements are the intervals
     * in seconds and DateTime interval
     *
     * @return array
     */
    protected function getTimeSpanIntervals()
    {
        return [
            1800 => [ // 30 mins - 30 intervals of 1 min
                'seconds' => '60',
                'interval' => '1M',
            ],
            3600 => [ // 60 mins - 30 intervals of 2 min
                'seconds' => '120',
                'interval' => '2M',
            ],
            7200 => [ // 2 hours - 30 intervals of 4 min
                'seconds' => '240',
                'interval' => '4M',
            ],
            10800 => [ // 3 hours - 36 intervals of 5 min
                'seconds' => '300',
                'interval' => '5M',
            ],
            21600 => [ // 6 hours - 36 intervals of 10 min
                'seconds' => '600',
                'interval' => '10M',
            ],
            43200 => [ // 12 hours - 36 intervals of 20 min
                'seconds' => '1200',
                'interval' => '20M',
            ],
            64800 => [ // 18 hours - 36 intervals of 30 min
                'seconds' => '1800',
                'interval' => '30M',
            ],
            86400 => [ // 1 day - 24 intervals of 1 hour
                'seconds' => '3600',
                'interval' => '1H',
            ],
            172800 => [ // 2 days - 24 intervals of 2 hours
                'seconds' => '7200',
                'interval' => '2H',
            ],
            345600 => [ // 4 days - 24 intervals of 4 hours
                'seconds' => '14400',
                'interval' => '4H',
            ],
            1296000 => [ // 15 days - 15 intervals of 1 day
                'seconds' => '86400',
                'interval' => '24H',
            ],
        ];
    }

    /**
     * Populate the periods into the intervals
     *
     * @param Collection $opensPerPeriod
     * @param DatePeriod $intervals
     * @return array
     */
    protected function populatePeriods(Collection $opensPerPeriod, DatePeriod $intervals): array
    {
        $periods = [];

        // create an array periods, where the key for each item is the date
        foreach ($intervals as $interval) {
            $periods[$interval->format('Y-m-d H:i:s')] = [
                'opened_at' => $interval->format('d-M H:i'),
                'open_count' => 0,
            ];
        }

        // Populate the actual opens per period into the intervals
        if ($opensPerPeriod) {
            foreach ($opensPerPeriod as $item) {
                $periods[$item->period_start]['open_count'] = $item->open_count;
            }
        }

        return $periods;
    }

    /**
     * Get all clicked links for a campaign
     *
     * @return mixed
     * @throws Exception
     */
    protected function getCampaignUrls()
    {
        return $this->messageUrlRepo->getBy([
            'source_type' => Campaign::class,
            'source_id' => $this->campaign->id,
        ]);
    }
}
