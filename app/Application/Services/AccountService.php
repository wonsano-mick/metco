<?php

namespace App\Application\Services;

use Ramsey\Uuid\Uuid;
use App\Domain\Entities\Account;
use App\Domain\ValueObjects\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Domain\Events\AccountCreated;
use Illuminate\Support\Facades\Event;
use App\Application\DTOs\CreateAccountDTO;
use App\Domain\ValueObjects\AccountNumber;
use App\Domain\Repositories\UserRepositoryInterface;
use App\Domain\Repositories\AccountRepositoryInterface;
use App\Domain\Repositories\AccountTypeRepositoryInterface;

class AccountService
{
    private AccountRepositoryInterface $accountRepository;
    private UserRepositoryInterface $userRepository;
    private AccountTypeRepositoryInterface $accountTypeRepository;
    private TransactionService $transactionService;
    private AuditService $auditService;

    public function __construct(
        AccountRepositoryInterface $accountRepository,
        UserRepositoryInterface $userRepository,
        AccountTypeRepositoryInterface $accountTypeRepository,
        TransactionService $transactionService,
        AuditService $auditService
    ) {
        $this->accountRepository = $accountRepository;
        $this->userRepository = $userRepository;
        $this->accountTypeRepository = $accountTypeRepository;
        $this->transactionService = $transactionService;
        $this->auditService = $auditService;
    }

    public function createAccount(CreateAccountDTO $dto): Account
    {
        return DB::transaction(function () use ($dto) {
            // Convert string to Uuid
            $userId = Uuid::fromString($dto->userId);

            // Validate user
            $user = $this->userRepository->findById($userId);
            if (!$user || !$user->isActive()) {
                throw new \DomainException('User account is not active or not found');
            }

            // Validate account type
            $accountType = $this->accountTypeRepository->findByCode($dto->accountType);
            if (!$accountType || !$accountType->isActive()) {
                throw new \DomainException('Account type is not available');
            }

            // Validate initial deposit
            $minBalance = new Money($accountType->getMinBalance());
            if ($dto->initialDeposit->isLessThan($minBalance)) {
                throw new \DomainException(
                    sprintf('Minimum deposit required: %s', $minBalance->format())
                );
            }

            // Generate account number
            $accountNumber = AccountNumber::generate($dto->currency === 'USD' ? 'US' : substr($dto->currency, 0, 2));

            // Create account entity
            $account = new Account(
                Uuid::uuid4(),
                $user->getTenantId(),
                $user->getId(),
                $accountNumber,
                $accountType->getId(),
                $dto->initialDeposit,
                $dto->currency
            );

            // Save account
            $this->accountRepository->save($account);

            // Create initial deposit transaction if amount > 0
            if ($dto->initialDeposit->isPositive()) {
                $this->transactionService->recordDeposit(
                    $account->getId()->toString(), // Convert to string for API
                    $dto->initialDeposit,
                    'Initial deposit',
                    $user->getId()->toString() // Convert to string
                );
            }

            // Dispatch domain event
            Event::dispatch(new AccountCreated(
                $account->getId(),
                $user->getId(),
                $accountNumber->getNumber(),
                $dto->accountType,
                $dto->initialDeposit
            ));

            // Audit trail
            $this->auditService->logAccountCreated(
                $user->getId()->toString(),
                $account->getId()->toString(),
                $accountNumber->getNumber(),
                $dto->accountType
            );

            return $account;
        });
    }

    public function getAccountDetails(string $accountId, string $userId): array
    {
        // Convert strings to Uuid
        $accountUuid = Uuid::fromString($accountId);
        $userUuid = Uuid::fromString($userId);

        $account = $this->accountRepository->findById($accountUuid);

        if (!$account) {
            throw new \DomainException('Account not found');
        }

        // Authorization check
        if (!$account->getUserId()->equals($userUuid)) {
            throw new \DomainException('Unauthorized access to account');
        }

        $accountType = $this->accountTypeRepository->findById($account->getAccountTypeId());

        return [
            'id' => $account->getId()->toString(),
            'accountNumber' => $account->getAccountNumber()->getFormatted(),
            'type' => $accountType ? $accountType->getName() : 'Unknown',
            'currentBalance' => $account->getCurrentBalance()->format(),
            'availableBalance' => $account->getAvailableBalance()->format(),
            'ledgerBalance' => $account->getLedgerBalance()->format(),
            'currency' => $account->getCurrency(),
            'status' => $account->getStatus(),
            'openedAt' => $account->getOpenedAt()->format('Y-m-d H:i:s'),
            'interestRate' => $accountType ? $accountType->getInterestRate() : '0',
            'minBalance' => $accountType ? $accountType->getMinBalance() : '0',
            'maxBalance' => $accountType ? $accountType->getMaxBalance() : '0',
        ];
    }

    public function getAccountStatement(
        string $accountId,
        string $userId,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate,
        int $page = 1,
        int $perPage = 50
    ): array {
        // Convert strings to Uuid
        $accountUuid = Uuid::fromString($accountId);
        $userUuid = Uuid::fromString($userId);

        $account = $this->accountRepository->findById($accountUuid);

        if (!$account) {
            throw new \DomainException('Account not found');
        }

        // Authorization check
        if (!$account->getUserId()->equals($userUuid)) {
            throw new \DomainException('Unauthorized access to account');
        }

        $transactions = $this->accountRepository->getTransactions(
            $accountUuid, // Use Uuid, not string
            $startDate,
            $endDate,
            $page,
            $perPage
        );

        $summary = $this->accountRepository->getTransactionSummary(
            $accountUuid, // Use Uuid, not string
            $startDate,
            $endDate
        );

        return [
            'account' => [
                'number' => $account->getAccountNumber()->getFormatted(),
                'currency' => $account->getCurrency(),
            ],
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'summary' => $summary,
            'transactions' => array_map(function ($transaction) {
                return [
                    'id' => $transaction['id'] ?? null,
                    'date' => $transaction['created_at'] ?? null,
                    'description' => $transaction['transaction']['description'] ?? null,
                    'reference' => $transaction['transaction']['reference'] ?? null,
                    'type' => $transaction['entry_type'] ?? null,
                    'amount' => $transaction['amount'] ?? '0',
                    'currency' => $transaction['currency'] ?? 'USD',
                    'balanceAfter' => $transaction['balance_after'] ?? '0',
                    'status' => $transaction['transaction']['status'] ?? null,
                ];
            }, $transactions),
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => count($transactions),
            ],
        ];
    }

    public function freezeAccount(string $accountId, string $adminUserId, string $reason): void
    {
        DB::transaction(function () use ($accountId, $adminUserId, $reason) {
            // Convert strings to Uuid
            $accountUuid = Uuid::fromString($accountId);

            $account = $this->accountRepository->findById($accountUuid);

            if (!$account) {
                throw new \DomainException('Account not found');
            }

            $account->freeze();
            $this->accountRepository->save($account);

            // Audit trail
            $this->auditService->logAccountFrozen(
                $adminUserId,
                $accountId,
                $account->getAccountNumber()->getNumber(),
                $reason
            );

            // Notify user
            $this->notifyUserAccountFrozen(
                $account->getUserId()->toString(), // Convert to string
                $account->getAccountNumber()->getFormatted(),
                $reason
            );
        });
    }

    // Add the missing notification method
    private function notifyUserAccountFrozen(
        string $userId,
        string $accountNumber,
        string $reason
    ): void {
        try {
            // Find user
            $userUuid = Uuid::fromString($userId);
            $user = $this->userRepository->findById($userUuid);

            if (!$user) {
                Log::warning("Cannot notify user about frozen account: User {$userId} not found");
                return;
            }

            // In a real app, you would send email/notification here
            // For now, just log it
            Log::info("Account frozen notification", [
                'user_id' => $userId,
                'account_number' => $accountNumber,
                'reason' => $reason,
                'email' => $user->getEmail()->getValue(),
                'timestamp' => now()->toISOString(),
            ]);

            // Example: Send email (uncomment when email is configured)
            /*
            Notification::send($user, new AccountFrozenNotification(
                $accountNumber,
                $reason,
                now()->toDateTimeString()
            ));
            */
        } catch (\Exception $e) {
            Log::error("Failed to send account frozen notification", [
                'user_id' => $userId,
                'account_number' => $accountNumber,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
