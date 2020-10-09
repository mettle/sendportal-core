<?php

declare(strict_types=1);

namespace Sendportal\Base\Presenters;

use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use Exception;
use Illuminate\Support\Collection;
use RuntimeException;
use Sendportal\Base\Models\Campaign;
use Sendportal\Base\Repositories\Campaigns\CampaignTenantRepositoryInterface;
use Sendportal\Base\Repositories\Messages\MessageTenantRepositoryInterface;
use Sendportal\Base\Repositories\MessageUrlRepository;

class CampaignReportPresenter
{
    /** @var Campaign */
    private $campaign;

    /** @var int */
    private $currentWorkspaceId;

    /** @var MessageTenantRepositoryInterface */
    private $messageRepo;

    /** @var MessageUrlRepository */
    private $messageUrlRepo;

    /** @var CampaignTenantRepositoryInterface */
    private $campaignRepo;

    /** @var int */
    private $interval;

    private const ONE_DAY_IN_SECONDS = 86400;
    private const THIRTY_DAYS_IN_SECONDS = self::ONE_DAY_IN_SECONDS * 30;

    public function __construct(Campaign $campaign, int $currentWorkspaceId, int $interval)
    {
        $this->messageRepo = app(MessageTenantRepositoryInterface::class);
        $this->messageUrlRepo = app(MessageUrlRepository::class);
        $this->campaignRepo = app(CampaignTenantRepositoryInterface::class);

        $this->campaign = $campaign;
        $this->currentWorkspaceId = $currentWorkspaceId;
        $this->interval = $interval;
    }

    /**
     * Generate the data for the view.
     *
     * @throws Exception
     */
    public function generate(): array
    {
        if (!$this->campaign) {
            throw new RuntimeException('Campaign must be initialised');
        }

        return [
            'chartData' => $this->getChartData(),
            'campaignUrls' => $this->getCampaignUrls(),
            'campaignStats' => $this->getCampaignStats(),
        ];
    }

    /**
     * Generate the chart data.
     *
     * @throws Exception
     */
    private function getChartData(): array
    {
        // Get the first event from the database.
        $first = $this->messageRepo->getFirstOpenedAt(
            $this->currentWorkspaceId,
            Campaign::class,
            $this->campaign->id
        );

        if (is_null($first)) {
            return [];
        }

        // Extract Carbon instances for $first and $last.
        [$first, $last] = $this->calculateFirstLast($first, $this->interval);

        // Calculate the timespan between the first and last event.
        $timespan = $this->calculateTimespan($first, $last);

        // Calculate the number of seconds for the given timespan.
        $secondsPerInterval = $this->calculateSecondsInterval($timespan);

        // Modify first so that it matches with the database intervals (i.e. using DIV in mysql).
        $first = Carbon::createFromTimestamp(floor($first->timestamp / $secondsPerInterval) * $secondsPerInterval);

        // Create the PHP DateTime intervals.
        $intervals = $this->createIntervals($first, $last, $timespan);

        // Calculate the opens per period from the database.
        $opensPerPeriod = $this->messageRepo->countUniqueOpensPerPeriod(
            $this->currentWorkspaceId,
            Campaign::class,
            $this->campaign->id,
            $secondsPerInterval
        );

        // Merge in the actual opens to the intervals.
        $periods = $this->populatePeriods($opensPerPeriod, $intervals);

        $result = [];

        // Separate the periods into labels and data for chart.js.
        /** @var array $period */
        foreach ($periods as $period) {
            $result['labels'][] = $period['opened_at'];
            $result['data'][] = $period['open_count'];
        }

        return $result;
    }

    /**
     * Create the DatePeriod intervals between the first and last opens.
     *
     * @throws Exception
     */
    private function createIntervals(Carbon $first, Carbon $last, int $timespan): DatePeriod
    {
        $interval = $this->calculateDateTimeInterval($timespan);

        return new DatePeriod(
            $first,
            new DateInterval('PT' . $interval),
            $last
        );
    }

    /**
     * Calculate the number of seconds between the first and last event, rounded to the nearest timespan interval.
     */
    private function calculateTimespan(Carbon $first, Carbon $last): int
    {
        /**
         * @var int $timespan
         * @var array $item
         */
        foreach ($this->getTimeSpanIntervals() as $timespan => $item) {
            if ($last->copy()->subHour()->lte($first->copy()->addSeconds($timespan))) {
                return $timespan;
            }
        }

        return self::THIRTY_DAYS_IN_SECONDS;
    }

    /**
     * Calculate the first and last timestamps.
     */
    private function calculateFirstLast(string $first, int $interval): array
    {
        $cFirst = Carbon::parse($first);
        $last = $cFirst->copy()->addHours($interval);

        return [$cFirst->copy()->startOfHour(), $last->copy()->endOfHour()];
    }

    /**
     * Calculate the DateTime interval for a given timespan.
     */
    private function calculateDateTimeInterval(int $timespan): string
    {
        $timespanIntervals = $this->getTimeSpanIntervals();

        if (isset($timespanIntervals[$timespan])) {
            return $timespanIntervals[$timespan]['interval'];
        }

        return '24H';
    }

    /**
     * Calculate the interval in seconds for a given timespan.
     */
    private function calculateSecondsInterval(int $timespan): int
    {
        $timespanIntervals = $this->getTimeSpanIntervals();

        if (isset($timespanIntervals[$timespan])) {
            return (int)$timespanIntervals[$timespan]['seconds'];
        }

        return self::ONE_DAY_IN_SECONDS;
    }

    /**
     * Map the timespan between the first and last event.
     *
     * The array key is the timespan and the elements are the intervals in seconds and DateTime interval.
     */
    private function getTimeSpanIntervals(): array
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
            43200 => [ // 12 hours - 36 intervals of 30 min
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
        ];
    }

    /**
     * Populate the periods into the intervals.
     */
    private function populatePeriods(Collection $opensPerPeriod, DatePeriod $intervals): array
    {
        $periods = [];

        // Create an array periods, where the key for each item is the date.
        /** @var Carbon $interval */
        foreach ($intervals as $interval) {
            $periods[$interval->format('Y-m-d H:i:s')] = [
                'opened_at' => $interval->format('d-M H:i'),
                'open_count' => 0,
            ];
        }

        // Populate the actual opens per period into the intervals.
        if ($opensPerPeriod) {
            foreach ($opensPerPeriod as $item) {
                if (array_key_exists($item->period_start, $periods)) {
                    $periods[$item->period_start]['open_count'] = $item->open_count;
                }
            }
        }

        return $periods;
    }

    /**
     * Get all clicked links for a campaign.
     *
     * @throws Exception
     */
    private function getCampaignUrls(): Collection
    {
        return $this->messageUrlRepo->getBy([
            'source_type' => Campaign::class,
            'source_id' => $this->campaign->id,
        ])->toBase();
    }

    /**
     * Get count and ratio statistics for a campaign.
     */
    private function getCampaignStats(): array
    {
        $countData = $this->campaignRepo->getCounts(collect($this->campaign->id), $this->currentWorkspaceId);

        return [
            'counts' => [
                'open' => (int) $countData[$this->campaign->id]->opened,
                'click' => (int) $countData[$this->campaign->id]->clicked,
                'sent' => $this->campaign->formatCount((int) $countData[$this->campaign->id]->sent),
                'bounce' => (int) $countData[$this->campaign->id]->bounced,
            ],
            'ratios' => [
                'open' => $this->campaign->getActionRatio((int) $countData[$this->campaign->id]->opened, (int) $countData[$this->campaign->id]->sent),
                'click' => $this->campaign->getActionRatio((int) $countData[$this->campaign->id]->clicked, (int) $countData[$this->campaign->id]->sent),
                'bounce' => $this->campaign->getActionRatio((int) $countData[$this->campaign->id]->bounced, (int) $countData[$this->campaign->id]->sent),
            ],
        ];
    }
}
