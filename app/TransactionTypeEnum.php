<?php

namespace App;

enum TransactionTypeEnum: string
{
    //

    case Credit = 'credit';
    case Debit = 'debit';
    case Transfer = 'transfer';
    case Deposit = 'deposit';
    case BillPayment = 'bill_payment';

}
