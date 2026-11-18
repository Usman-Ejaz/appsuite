<?php

namespace Domains\Ecommerce\Enums;

/**
 * The Ecommerce permissions a role can be granted. Each value is the permission's `name`, in the
 * `ecommerce:<resource>:<action>` shape the authorization gate checks; its label and description live in
 * `EcommercePermissionSeeder`.
 */
enum EcommercePermission: string
{
    case PRODUCT_VIEW = 'ecommerce:products:view';
    case PRODUCT_CREATE = 'ecommerce:products:create';
    case PRODUCT_UPDATE = 'ecommerce:products:update';
    case PRODUCT_DELETE = 'ecommerce:products:delete';

    case BRAND_VIEW = 'ecommerce:brands:view';
    case BRAND_CREATE = 'ecommerce:brands:create';
    case BRAND_UPDATE = 'ecommerce:brands:update';
    case BRAND_DELETE = 'ecommerce:brands:delete';

    case COLLECTION_VIEW = 'ecommerce:collections:view';
    case COLLECTION_CREATE = 'ecommerce:collections:create';
    case COLLECTION_UPDATE = 'ecommerce:collections:update';
    case COLLECTION_DELETE = 'ecommerce:collections:delete';

    case REVIEW_VIEW = 'ecommerce:reviews:view';
    case REVIEW_CREATE = 'ecommerce:reviews:create';
    case REVIEW_UPDATE = 'ecommerce:reviews:update';
    case REVIEW_DELETE = 'ecommerce:reviews:delete';
    case REVIEW_MODERATE = 'ecommerce:reviews:moderate';

    case ORDER_VIEW = 'ecommerce:orders:view';
    case ORDER_CREATE = 'ecommerce:orders:create';
    case ORDER_UPDATE = 'ecommerce:orders:update';
    case ORDER_DELETE = 'ecommerce:orders:delete';
    case ORDER_CANCEL = 'ecommerce:orders:cancel';
    case ORDER_APPLY_COUPON = 'ecommerce:orders:apply-coupon';

    case COUPON_VIEW = 'ecommerce:coupons:view';
    case COUPON_CREATE = 'ecommerce:coupons:create';
    case COUPON_UPDATE = 'ecommerce:coupons:update';
    case COUPON_DELETE = 'ecommerce:coupons:delete';

    case CAMPAIGN_VIEW = 'ecommerce:campaigns:view';
    case CAMPAIGN_CREATE = 'ecommerce:campaigns:create';
    case CAMPAIGN_UPDATE = 'ecommerce:campaigns:update';
    case CAMPAIGN_DELETE = 'ecommerce:campaigns:delete';

    case CATEGORY_VIEW = 'ecommerce:categories:view';
    case CATEGORY_MANAGE = 'ecommerce:categories:manage';

    case PAYMENT_METHOD_VIEW = 'ecommerce:payment-methods:view';
    case PAYMENT_METHOD_CREATE = 'ecommerce:payment-methods:create';
    case PAYMENT_METHOD_UPDATE = 'ecommerce:payment-methods:update';
    case PAYMENT_METHOD_DELETE = 'ecommerce:payment-methods:delete';

    case CUSTOMER_VIEW = 'ecommerce:customers:view';
    case CUSTOMER_CREATE = 'ecommerce:customers:create';
    case CUSTOMER_UPDATE = 'ecommerce:customers:update';
    case CUSTOMER_DELETE = 'ecommerce:customers:delete';
}
