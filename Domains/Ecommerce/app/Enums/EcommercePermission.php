<?php

namespace Domains\Ecommerce\Enums;

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

    public function label(): string
    {
        return match ($this) {
            self::PRODUCT_VIEW => 'View Products',
            self::PRODUCT_CREATE => 'Create Products',
            self::PRODUCT_UPDATE => 'Update Products',
            self::PRODUCT_DELETE => 'Delete Products',

            self::BRAND_VIEW => 'View Brands',
            self::BRAND_CREATE => 'Create Brands',
            self::BRAND_UPDATE => 'Update Brands',
            self::BRAND_DELETE => 'Delete Brands',

            self::COLLECTION_VIEW => 'View Collections',
            self::COLLECTION_CREATE => 'Create Collections',
            self::COLLECTION_UPDATE => 'Update Collections',
            self::COLLECTION_DELETE => 'Delete Collections',

            self::REVIEW_VIEW => 'View Reviews',
            self::REVIEW_CREATE => 'Create Reviews',
            self::REVIEW_UPDATE => 'Update Reviews',
            self::REVIEW_DELETE => 'Delete Reviews',
            self::REVIEW_MODERATE => 'Moderate Reviews',

            self::ORDER_VIEW => 'View Orders',
            self::ORDER_CREATE => 'Create Orders',
            self::ORDER_UPDATE => 'Update Orders',
            self::ORDER_DELETE => 'Delete Orders',
            self::ORDER_CANCEL => 'Cancel Orders',
            self::ORDER_APPLY_COUPON => 'Apply Order Coupons',

            self::COUPON_VIEW => 'View Coupons',
            self::COUPON_CREATE => 'Create Coupons',
            self::COUPON_UPDATE => 'Update Coupons',
            self::COUPON_DELETE => 'Delete Coupons',

            self::CAMPAIGN_VIEW => 'View Campaigns',
            self::CAMPAIGN_CREATE => 'Create Campaigns',
            self::CAMPAIGN_UPDATE => 'Update Campaigns',
            self::CAMPAIGN_DELETE => 'Delete Campaigns',

            self::CATEGORY_VIEW => 'View Categories',
            self::CATEGORY_MANAGE => 'Manage Categories',

            self::PAYMENT_METHOD_VIEW => 'View Payment Methods',
            self::PAYMENT_METHOD_CREATE => 'Create Payment Methods',
            self::PAYMENT_METHOD_UPDATE => 'Update Payment Methods',
            self::PAYMENT_METHOD_DELETE => 'Delete Payment Methods',

            self::CUSTOMER_VIEW => 'View Customers',
            self::CUSTOMER_CREATE => 'Create Customers',
            self::CUSTOMER_UPDATE => 'Update Customers',
            self::CUSTOMER_DELETE => 'Delete Customers',
        };
    }

    public function code(): string
    {
        return match ($this) {
            self::PRODUCT_VIEW => 'ecommerce_products_view',
            self::PRODUCT_CREATE => 'ecommerce_products_create',
            self::PRODUCT_UPDATE => 'ecommerce_products_update',
            self::PRODUCT_DELETE => 'ecommerce_products_delete',

            self::BRAND_VIEW => 'ecommerce_brands_view',
            self::BRAND_CREATE => 'ecommerce_brands_create',
            self::BRAND_UPDATE => 'ecommerce_brands_update',
            self::BRAND_DELETE => 'ecommerce_brands_delete',

            self::COLLECTION_VIEW => 'ecommerce_collections_view',
            self::COLLECTION_CREATE => 'ecommerce_collections_create',
            self::COLLECTION_UPDATE => 'ecommerce_collections_update',
            self::COLLECTION_DELETE => 'ecommerce_collections_delete',

            self::REVIEW_VIEW => 'ecommerce_reviews_view',
            self::REVIEW_CREATE => 'ecommerce_reviews_create',
            self::REVIEW_UPDATE => 'ecommerce_reviews_update',
            self::REVIEW_DELETE => 'ecommerce_reviews_delete',
            self::REVIEW_MODERATE => 'ecommerce_reviews_moderate',

            self::ORDER_VIEW => 'ecommerce_orders_view',
            self::ORDER_CREATE => 'ecommerce_orders_create',
            self::ORDER_UPDATE => 'ecommerce_orders_update',
            self::ORDER_DELETE => 'ecommerce_orders_delete',
            self::ORDER_CANCEL => 'ecommerce_orders_cancel',
            self::ORDER_APPLY_COUPON => 'ecommerce_orders_apply_coupon',

            self::COUPON_VIEW => 'ecommerce_coupons_view',
            self::COUPON_CREATE => 'ecommerce_coupons_create',
            self::COUPON_UPDATE => 'ecommerce_coupons_update',
            self::COUPON_DELETE => 'ecommerce_coupons_delete',

            self::CAMPAIGN_VIEW => 'ecommerce_campaigns_view',
            self::CAMPAIGN_CREATE => 'ecommerce_campaigns_create',
            self::CAMPAIGN_UPDATE => 'ecommerce_campaigns_update',
            self::CAMPAIGN_DELETE => 'ecommerce_campaigns_delete',

            self::CATEGORY_VIEW => 'ecommerce_categories_view',
            self::CATEGORY_MANAGE => 'ecommerce_categories_manage',

            self::PAYMENT_METHOD_VIEW => 'ecommerce_payment_methods_view',
            self::PAYMENT_METHOD_CREATE => 'ecommerce_payment_methods_create',
            self::PAYMENT_METHOD_UPDATE => 'ecommerce_payment_methods_update',
            self::PAYMENT_METHOD_DELETE => 'ecommerce_payment_methods_delete',

            self::CUSTOMER_VIEW => 'ecommerce_customers_view',
            self::CUSTOMER_CREATE => 'ecommerce_customers_create',
            self::CUSTOMER_UPDATE => 'ecommerce_customers_update',
            self::CUSTOMER_DELETE => 'ecommerce_customers_delete',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::PRODUCT_VIEW => 'View the product catalog, including pricing, stock, and variant details.',
            self::PRODUCT_CREATE => 'Add new products to the catalog.',
            self::PRODUCT_UPDATE => 'Edit existing product details, pricing, and stock levels.',
            self::PRODUCT_DELETE => 'Remove products from the catalog.',

            self::BRAND_VIEW => 'View the list of product brands.',
            self::BRAND_CREATE => 'Add new product brands.',
            self::BRAND_UPDATE => 'Edit existing product brands.',
            self::BRAND_DELETE => 'Remove product brands.',

            self::COLLECTION_VIEW => 'View merchandising collections and the products grouped within them.',
            self::COLLECTION_CREATE => 'Create new merchandising collections.',
            self::COLLECTION_UPDATE => 'Edit collections and manage which products belong to them.',
            self::COLLECTION_DELETE => 'Remove merchandising collections.',

            self::REVIEW_VIEW => 'View customer reviews left on products.',
            self::REVIEW_CREATE => 'Submit a review on behalf of a customer.',
            self::REVIEW_UPDATE => 'Edit the content of an existing review.',
            self::REVIEW_DELETE => 'Remove a review.',
            self::REVIEW_MODERATE => 'Approve or reject reviews awaiting moderation.',

            self::ORDER_VIEW => 'View orders, their line items, and totals.',
            self::ORDER_CREATE => 'Create new orders.',
            self::ORDER_UPDATE => 'Edit order details and manage line items.',
            self::ORDER_DELETE => 'Permanently remove an order.',
            self::ORDER_CANCEL => 'Cancel an order, restocking its items and reversing any applied coupon usage.',
            self::ORDER_APPLY_COUPON => 'Apply or remove a coupon on an order.',

            self::COUPON_VIEW => 'View discount coupons and their usage.',
            self::COUPON_CREATE => 'Create new discount coupons.',
            self::COUPON_UPDATE => 'Edit existing discount coupons.',
            self::COUPON_DELETE => 'Remove discount coupons.',

            self::CAMPAIGN_VIEW => 'View marketing campaigns.',
            self::CAMPAIGN_CREATE => 'Create new marketing campaigns.',
            self::CAMPAIGN_UPDATE => 'Edit existing marketing campaigns.',
            self::CAMPAIGN_DELETE => 'Remove marketing campaigns.',

            self::CATEGORY_VIEW => 'View product categories.',
            self::CATEGORY_MANAGE => 'Create, edit, and delete product categories.',

            self::PAYMENT_METHOD_VIEW => 'View the company\'s configured checkout payment methods.',
            self::PAYMENT_METHOD_CREATE => 'Add a new checkout payment method.',
            self::PAYMENT_METHOD_UPDATE => 'Edit an existing checkout payment method.',
            self::PAYMENT_METHOD_DELETE => 'Remove a checkout payment method.',

            self::CUSTOMER_VIEW => 'View customer records.',
            self::CUSTOMER_CREATE => 'Add new customer records.',
            self::CUSTOMER_UPDATE => 'Edit existing customer records.',
            self::CUSTOMER_DELETE => 'Remove customer records.',
        };
    }
}
