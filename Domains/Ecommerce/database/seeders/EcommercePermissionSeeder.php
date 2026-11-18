<?php

namespace Domains\Ecommerce\Database\Seeders;

use Domains\Core\Enums\AppCode;
use Domains\Ecommerce\Enums\EcommercePermission;
use Domains\Identity\Database\Seeders\BasePermissionSeeder;

class EcommercePermissionSeeder extends BasePermissionSeeder
{
    protected function appCode(): AppCode
    {
        return AppCode::ECOMMERCE;
    }

    protected function data(): array
    {
        return [
            [
                'permission' => EcommercePermission::PRODUCT_VIEW,
                'label' => 'View Products',
                'description' => 'View the product catalog, including pricing, stock, and variant details.',
            ],
            [
                'permission' => EcommercePermission::PRODUCT_CREATE,
                'label' => 'Create Products',
                'description' => 'Add new products to the catalog.',
            ],
            [
                'permission' => EcommercePermission::PRODUCT_UPDATE,
                'label' => 'Update Products',
                'description' => 'Edit existing product details, pricing, and stock levels.',
            ],
            [
                'permission' => EcommercePermission::PRODUCT_DELETE,
                'label' => 'Delete Products',
                'description' => 'Remove products from the catalog.',
            ],
            [
                'permission' => EcommercePermission::BRAND_VIEW,
                'label' => 'View Brands',
                'description' => 'View the list of product brands.',
            ],
            [
                'permission' => EcommercePermission::BRAND_CREATE,
                'label' => 'Create Brands',
                'description' => 'Add new product brands.',
            ],
            [
                'permission' => EcommercePermission::BRAND_UPDATE,
                'label' => 'Update Brands',
                'description' => 'Edit existing product brands.',
            ],
            [
                'permission' => EcommercePermission::BRAND_DELETE,
                'label' => 'Delete Brands',
                'description' => 'Remove product brands.',
            ],
            [
                'permission' => EcommercePermission::COLLECTION_VIEW,
                'label' => 'View Collections',
                'description' => 'View merchandising collections and the products grouped within them.',
            ],
            [
                'permission' => EcommercePermission::COLLECTION_CREATE,
                'label' => 'Create Collections',
                'description' => 'Create new merchandising collections.',
            ],
            [
                'permission' => EcommercePermission::COLLECTION_UPDATE,
                'label' => 'Update Collections',
                'description' => 'Edit collections and manage which products belong to them.',
            ],
            [
                'permission' => EcommercePermission::COLLECTION_DELETE,
                'label' => 'Delete Collections',
                'description' => 'Remove merchandising collections.',
            ],
            [
                'permission' => EcommercePermission::REVIEW_VIEW,
                'label' => 'View Reviews',
                'description' => 'View customer reviews left on products.',
            ],
            [
                'permission' => EcommercePermission::REVIEW_CREATE,
                'label' => 'Create Reviews',
                'description' => 'Submit a review on behalf of a customer.',
            ],
            [
                'permission' => EcommercePermission::REVIEW_UPDATE,
                'label' => 'Update Reviews',
                'description' => 'Edit the content of an existing review.',
            ],
            [
                'permission' => EcommercePermission::REVIEW_DELETE,
                'label' => 'Delete Reviews',
                'description' => 'Remove a review.',
            ],
            [
                'permission' => EcommercePermission::REVIEW_MODERATE,
                'label' => 'Moderate Reviews',
                'description' => 'Approve or reject reviews awaiting moderation.',
            ],
            [
                'permission' => EcommercePermission::ORDER_VIEW,
                'label' => 'View Orders',
                'description' => 'View orders, their line items, and totals.',
            ],
            [
                'permission' => EcommercePermission::ORDER_CREATE,
                'label' => 'Create Orders',
                'description' => 'Create new orders.',
            ],
            [
                'permission' => EcommercePermission::ORDER_UPDATE,
                'label' => 'Update Orders',
                'description' => 'Edit order details and manage line items.',
            ],
            [
                'permission' => EcommercePermission::ORDER_DELETE,
                'label' => 'Delete Orders',
                'description' => 'Permanently remove an order.',
            ],
            [
                'permission' => EcommercePermission::ORDER_CANCEL,
                'label' => 'Cancel Orders',
                'description' => 'Cancel an order, restocking its items and reversing any applied coupon usage.',
            ],
            [
                'permission' => EcommercePermission::ORDER_APPLY_COUPON,
                'label' => 'Apply Order Coupons',
                'description' => 'Apply or remove a coupon on an order.',
            ],
            [
                'permission' => EcommercePermission::COUPON_VIEW,
                'label' => 'View Coupons',
                'description' => 'View discount coupons and their usage.',
            ],
            [
                'permission' => EcommercePermission::COUPON_CREATE,
                'label' => 'Create Coupons',
                'description' => 'Create new discount coupons.',
            ],
            [
                'permission' => EcommercePermission::COUPON_UPDATE,
                'label' => 'Update Coupons',
                'description' => 'Edit existing discount coupons.',
            ],
            [
                'permission' => EcommercePermission::COUPON_DELETE,
                'label' => 'Delete Coupons',
                'description' => 'Remove discount coupons.',
            ],
            [
                'permission' => EcommercePermission::CAMPAIGN_VIEW,
                'label' => 'View Campaigns',
                'description' => 'View marketing campaigns.',
            ],
            [
                'permission' => EcommercePermission::CAMPAIGN_CREATE,
                'label' => 'Create Campaigns',
                'description' => 'Create new marketing campaigns.',
            ],
            [
                'permission' => EcommercePermission::CAMPAIGN_UPDATE,
                'label' => 'Update Campaigns',
                'description' => 'Edit existing marketing campaigns.',
            ],
            [
                'permission' => EcommercePermission::CAMPAIGN_DELETE,
                'label' => 'Delete Campaigns',
                'description' => 'Remove marketing campaigns.',
            ],
            [
                'permission' => EcommercePermission::CATEGORY_VIEW,
                'label' => 'View Categories',
                'description' => 'View product categories.',
            ],
            [
                'permission' => EcommercePermission::CATEGORY_MANAGE,
                'label' => 'Manage Categories',
                'description' => 'Create, edit, and delete product categories.',
            ],
            [
                'permission' => EcommercePermission::PAYMENT_METHOD_VIEW,
                'label' => 'View Payment Methods',
                'description' => "View the company's configured checkout payment methods.",
            ],
            [
                'permission' => EcommercePermission::PAYMENT_METHOD_CREATE,
                'label' => 'Create Payment Methods',
                'description' => 'Add a new checkout payment method.',
            ],
            [
                'permission' => EcommercePermission::PAYMENT_METHOD_UPDATE,
                'label' => 'Update Payment Methods',
                'description' => 'Edit an existing checkout payment method.',
            ],
            [
                'permission' => EcommercePermission::PAYMENT_METHOD_DELETE,
                'label' => 'Delete Payment Methods',
                'description' => 'Remove a checkout payment method.',
            ],
            [
                'permission' => EcommercePermission::CUSTOMER_VIEW,
                'label' => 'View Customers',
                'description' => 'View customer records.',
            ],
            [
                'permission' => EcommercePermission::CUSTOMER_CREATE,
                'label' => 'Create Customers',
                'description' => 'Add new customer records.',
            ],
            [
                'permission' => EcommercePermission::CUSTOMER_UPDATE,
                'label' => 'Update Customers',
                'description' => 'Edit existing customer records.',
            ],
            [
                'permission' => EcommercePermission::CUSTOMER_DELETE,
                'label' => 'Delete Customers',
                'description' => 'Remove customer records.',
            ],
        ];
    }
}
