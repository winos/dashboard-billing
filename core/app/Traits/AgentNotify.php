<?php
namespace App\Traits;

use App\Constants\Status;

trait AgentNotify
{
    public static function notifyToUser()
    {
        return [
            'allAgents'               => 'All Agents',
            'selectedAgents'          => 'Selected Agents',
            'moneyInAgent'            => 'Money In Agents',
            'notMoneyInAgent'         => 'Not Money In Agents',
            'kycUnverified'           => 'Kyc Unverified Agents',
            'kycVerified'             => 'Kyc Verified Agents',
            'kycPending'              => 'Kyc Pending Agents',
            'withBalance'             => 'With Balance Agents',
            'emptyBalanceAgents'      => 'Empty Balance Agents',
            'twoFaDisableAgents'      => '2FA Disable Agent',
            'twoFaEnableAgents'       => '2FA Enable Agent',
            'hasDepositedAgents'      => 'Add Money Agents',
            'notDepositedAgents'      => 'Not Add Money Agents',
            'pendingDepositedAgents'  => 'Pending Add Money Agents',
            'rejectedDepositedAgents' => 'Rejected Add Money Agents',
            'topDepositedAgents'      => 'Top Add Money Agents',
            'hasWithdrawAgents'       => 'Withdraw Agents',
            'pendingWithdrawAgents'   => 'Pending Withdraw Agents',
            'rejectedWithdrawAgents'  => 'Rejected Withdraw Agents',
            'pendingTicketAgent'      => 'Pending Ticket Agents',
            'answerTicketAgent'       => 'Answer Ticket Agents',
            'closedTicketAgent'       => 'Closed Ticket Agents',
            'notLoginAgents'          => 'Last Few Days Not Login Agents',
        ];
    }

    public function scopeSelectedAgents($query)
    {
        return $query->whereIn('id', request()->user ?? []);
    }

    public function scopeAllAgents($query)
    {
        return $query;
    }

    public function scopeMoneyInAgent($query)
    {
        return $query->whereHas('transactions', function ($trx) {
            return $trx->where('remark', 'money_in');
        });
    }
    public function scopeNotMoneyInAgent($query)
    {
        return $query->whereDoesntHave('transactions', function ($trx) {
            return $trx->where('remark', 'money_in');
        });
    }
    public function scopeEmptyBalanceAgents($query)
    {
        return $query->where('balance', '<=', 0);
    }

    public function scopeTwoFaDisableAgents($query)
    {
        return $query->where('ts', Status::DISABLE);
    }

    public function scopeTwoFaEnableAgents($query)
    {
        return $query->where('ts', Status::ENABLE);
    }

    public function scopeHasDepositedAgents($query)
    {
        return $query->whereHas('deposits', function ($deposit) {
            $deposit->successful();
        });
    }

    public function scopeNotDepositedAgents($query)
    {
        return $query->whereDoesntHave('deposits', function ($q) {
            $q->successful();
        });
    }

    public function scopePendingDepositedAgents($query)
    {
        return $query->whereHas('deposits', function ($deposit) {
            $deposit->pending();
        });
    }

    public function scopeRejectedDepositedAgents($query)
    {
        return $query->whereHas('deposits', function ($deposit) {
            $deposit->rejected();
        });
    }

    public function scopeTopDepositedAgents($query)
    {
        return $query->whereHas('deposits', function ($deposit) {
            $deposit->successful();
        })->withSum(['deposits' => function ($q) {
            $q->successful();
        }], 'amount')->orderBy('deposits_sum_amount', 'desc')->take(request()->number_of_top_deposited_user ?? 10);
    }

    public function scopeHasWithdrawAgents($query)
    {
        return $query->whereHas('withdrawals', function ($q) {
            $q->approved();
        });
    }

    public function scopePendingWithdrawAgents($query)
    {
        return $query->whereHas('withdrawals', function ($q) {
            $q->pending();
        });
    }

    public function scopeRejectedWithdrawAgents($query)
    {
        return $query->whereHas('withdrawals', function ($q) {
            $q->rejected();
        });
    }

    public function scopePendingTicketAgent($query)
    {
        return $query->whereHas('tickets', function ($q) {
            $q->whereIn('status', [Status::TICKET_OPEN, Status::TICKET_REPLY]);
        });
    }

    public function scopeClosedTicketAgent($query)
    {
        return $query->whereHas('tickets', function ($q) {
            $q->where('status', Status::TICKET_CLOSE);
        });
    }

    public function scopeAnswerTicketAgent($query)
    {
        return $query->whereHas('tickets', function ($q) {

            $q->where('status', Status::TICKET_ANSWER);
        });
    }

    public function scopeNotLoginAgents($query)
    {
        return $query->whereDoesntHave('loginLogs', function ($q) {
            $q->whereDate('created_at', '>=', now()->subDays(request()->number_of_days ?? 10));
        });
    }

    public function scopeKycVerified($query)
    {
        return $query->where('kv', Status::KYC_VERIFIED);
    }

}
