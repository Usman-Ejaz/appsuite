<?php

namespace Domains\Shared\Enums;

/**
 * The kind of payment method a company offers at checkout.
 */
enum PaymentMethodType: string
{
    /**
     * Payment is collected in physical cash, typically on delivery or at pickup.
     */
    case CASH = 'Cash';

    /**
     * The customer transfers funds directly into a bank account.
     */
    case BANK_TRANSFER = 'Bank Transfer';

    /**
     * Payment is collected using a credit or debit card.
     */
    case CARD = 'Card';

    /**
     * Payment is collected through a digital wallet.
     */
    case WALLET = 'Wallet';

    /**
     * Payment is collected when item is delivered.
     */
    case CASH_ON_DELIEVRY = 'Cash On Delivery';

    /**
     * A payment method that doesn't fit any of the other types.
     */
    case OTHER = 'Other';
}
