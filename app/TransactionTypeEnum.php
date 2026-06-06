<?php

namespace App;

enum TransactionType:string
{
    //
    

    case Credit = 'credit';
    case Debit = 'debit';
    case Transfer = 'transfer';
    case Deposit = 'deposit';
    case BillPayment = 'bill_payment';

}
