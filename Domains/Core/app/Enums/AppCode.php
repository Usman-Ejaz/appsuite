<?php

namespace Domains\Core\Enums;

enum AppCode: string
{
    case HR = 'hr';
    case CRM = 'crm';
    case CMS = 'cms';
    case SYSTEM = 'system';
    case STORAGE = 'storage';
    case FINANCE = 'finance';
    case ECOMMERCE = 'ecommerce';
    case INVENTORY = 'inventory';
    case FRONT_DESK = 'front-desk';
    case SUPPORT = 'support';
}
