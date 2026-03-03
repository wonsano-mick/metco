<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Transaction\AutomatedFeeService;
use App\Services\Transaction\AutomatedInterestService;
use Carbon\Carbon;

class ProcessAutomatedFees extends Command
{
    protected $signature = 'banking:process-automated-fees 
                            {--date= : The date to process fees for (Y-m-d)}
                            {--type=all : Type of processing (fees, interest, all)}
                            {--generate-only : Only generate pending entries, don\'t process}';

    protected $description = 'Process automated fees and interest for accounts';

    protected AutomatedFeeService $feeService;
    protected AutomatedInterestService $interestService;

    public function __construct()
    {
        parent::__construct();

        // Use system user ID (you may need to adjust this)
        $systemUserId = 1; // System user ID
        $this->feeService = new AutomatedFeeService($systemUserId);
        $this->interestService = new AutomatedInterestService($systemUserId);
    }

    public function handle()
    {
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : now();
        $type = $this->option('type');
        $generateOnly = $this->option('generate-only');

        $this->info("Starting automated processing for date: {$date->format('Y-m-d')}");
        $this->newLine();

        if ($type === 'all' || $type === 'fees') {
            $this->processFees($date, $generateOnly);
        }

        if ($type === 'all' || $type === 'interest') {
            $this->processInterest($date, $generateOnly);
        }

        $this->newLine();
        $this->info('Automated processing completed successfully!');
    }

    protected function processFees(Carbon $date, bool $generateOnly): void
    {
        $this->info('Processing fees...');

        if ($generateOnly) {
            $results = $this->feeService->generatePendingFees($date);
            $this->displayFeeGenerationResults($results);
        } else {
            // First generate pending fees
            $generationResults = $this->feeService->generatePendingFees($date);
            $this->displayFeeGenerationResults($generationResults);

            // Then process them
            $this->newLine();
            $this->info('Processing pending fees...');
            $processingResults = $this->feeService->processPendingFees($date);
            $this->displayFeeProcessingResults($processingResults);
        }
    }

    protected function processInterest(Carbon $date, bool $generateOnly): void
    {
        $this->info('Processing interest...');

        if ($generateOnly) {
            $results = $this->interestService->generatePendingInterest($date);
            $this->displayInterestGenerationResults($results);
        } else {
            // First generate pending interest
            $generationResults = $this->interestService->generatePendingInterest($date);
            $this->displayInterestGenerationResults($generationResults);

            // Then process it
            $this->newLine();
            $this->info('Processing pending interest...');
            $processingResults = $this->interestService->processPendingInterest($date);
            $this->displayInterestProcessingResults($processingResults);
        }
    }

    protected function displayFeeGenerationResults(array $results): void
    {
        $this->table(
            ['Metric', 'Value'],
            [
                ['Accounts Processed', $results['total_processed']],
                ['Fees Generated', $results['total_fees_generated']],
                ['Total Amount', number_format($results['total_amount'], 2)],
            ]
        );

        if (!empty($results['errors'])) {
            $this->error('Errors occurred:');
            foreach ($results['errors'] as $error) {
                $this->error("- {$error['configuration']}: {$error['error']}");
            }
        }
    }

    protected function displayFeeProcessingResults(array $results): void
    {
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Processed', $results['total_processed']],
                ['Successful', $results['successful']],
                ['Failed', $results['failed']],
                ['Waived', $results['waived']],
                ['Total Amount', number_format($results['total_amount'], 2)],
            ]
        );

        if (!empty($results['errors'])) {
            $this->error('Errors occurred:');
            foreach ($results['errors'] as $error) {
                $this->error("- Fee {$error['fee_id']} (Account {$error['account_id']}): {$error['error']}");
            }
        }
    }

    protected function displayInterestGenerationResults(array $results): void
    {
        $this->table(
            ['Metric', 'Value'],
            [
                ['Accounts Processed', $results['total_processed']],
                ['Interest Records Generated', $results['total_interest_generated']],
                ['Total Amount', number_format($results['total_amount'], 2)],
            ]
        );

        if (!empty($results['errors'])) {
            $this->error('Errors occurred:');
            foreach ($results['errors'] as $error) {
                $this->error("- {$error['configuration']}: {$error['error']}");
            }
        }
    }

    protected function displayInterestProcessingResults(array $results): void
    {
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Processed', $results['total_processed']],
                ['Successful', $results['successful']],
                ['Failed', $results['failed']],
                ['Total Amount', number_format($results['total_amount'], 2)],
            ]
        );

        if (!empty($results['errors'])) {
            $this->error('Errors occurred:');
            foreach ($results['errors'] as $error) {
                $this->error("- Interest {$error['interest_id']} (Account {$error['account_id']}): {$error['error']}");
            }
        }
    }
}
