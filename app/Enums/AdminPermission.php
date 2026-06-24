<?php

namespace App\Enums;

enum AdminPermission: string
{
    case Dashboard = 'dashboard';
    case Products = 'products';
    case Categories = 'categories';
    case Orders = 'orders';
    case Customers = 'customers';
    case Users = 'users';
    case Content = 'content';
    case Reports = 'reports';

    public function label(): string
    {
        return match ($this) {
            self::Dashboard => 'Dashboard',
            self::Products => 'Products',
            self::Categories => 'Categories',
            self::Orders => 'Orders',
            self::Customers => 'Customers',
            self::Users => 'Admin users',
            self::Content => 'Website content',
            self::Reports => 'Reports',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Dashboard => 'View dashboard overview and analytics',
            self::Products => 'Create, edit, and remove products',
            self::Categories => 'Manage product categories',
            self::Orders => 'View and update orders',
            self::Customers => 'View and manage customer accounts',
            self::Users => 'Create and manage admin users and permissions',
            self::Content => 'Edit homepage sections, store settings, testimonials, and legal pages',
            self::Reports => 'View and export sales reports',
        };
    }

    /**
     * @return list<self>
     */
    public static function all(): array
    {
        return self::cases();
    }
}
