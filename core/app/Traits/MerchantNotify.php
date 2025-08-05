<?php
namespace App\Traits;

use App\Constants\Status;

trait MerchantNotify
{
    public static function notifyToUser()
    {
        return [
            'allMerchants'               => 'All Merchants',
            'selectedMerchants'          => 'Selected Merchants',
            'kycUnverified'              => 'Kyc Unverified Merchants',
            'kycVerified'                => 'Kyc Verified Merchants',
            'kycPending'                 => 'Kyc Pending Merchants',
            'withBalance'                => 'With Balance Merchants',
            'emptyBalanceMerchants'      => 'Empty Balance Merchants',
            'twoFaDisableMerchants'      => '2FA Disable Merchant',
            'twoFaEnableMerchants'       => '2FA Enable Merchant',
            'hasWithdrawMerchants'       => 'Withdraw Merchants',
            'pendingWithdrawMerchants'   => 'Pending Withdraw Merchants',
            'rejectedWithdrawMerchants'  => 'Rejected Withdraw Merchants',
            'pendingTicketMerchant'      => 'Pending Ticket Merchants',
            'answerTicketMerchant'       => 'Answer Ticket Merchants',
            'closedTicketMerchant'       => 'Closed Ticket Merchants',
            'notLoginMerchants'          => 'Last Few Days Not Login Merchants',
        ];
    }

    public function scopeSelectedMerchants($query)
    {
        return $query->whereIn('id', request()->user ?? []);
    }

    public function scopeAllMerchants($query)
    {
        return $query;
    }

    public function scopeEmptyBalanceMerchants($query)
    {
        return $query->where('balance', '<=', 0);
    }

    public function scopeTwoFaDisableMerchants($query)
    {
        return $query->where('ts', Status::DISABLE);
    }

    public function scopeTwoFaEnableMerchants($query)
    {
        return $query->where('ts', Status::ENABLE);
    }

    public function scopeHasWithdrawMerchants($query)
    {
        return $query->whereHas('withdrawals', function ($q) {
            $q->approved();
        });
    }

    public function scopePendingWithdrawMerchants($query)
    {
        return $query->whereHas('withdrawals', function ($q) {
            $q->pending();
        });
    }

    public function scopeRejectedWithdrawMerchants($query)
    {
        return $query->whereHas('withdrawals', function ($q) {
            $q->rejected();
        });
    }

    public function scopePendingTicketMerchant($query)
    {
        return $query->whereHas('tickets', function ($q) {
            $q->whereIn('status', [Status::TICKET_OPEN, Status::TICKET_REPLY]);
        });
    }

    public function scopeClosedTicketMerchant($query)
    {
        return $query->whereHas('tickets', function ($q) {
            $q->where('status', Status::TICKET_CLOSE);
        });
    }

    public function scopeAnswerTicketMerchant($query)
    {
        return $query->whereHas('tickets', function ($q) {

            $q->where('status', Status::TICKET_ANSWER);
        });
    }

    public function scopeNotLoginMerchants($query)
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
