<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(Request $request): View
    {
        $query = trim($request->string('q')->toString());

        if ($query === '') {
            return view('admin.search', [
                'query' => '',
                'products' => collect(),
                'orders' => collect(),
                'customers' => collect(),
                'categories' => collect(),
            ]);
        }

        $like = '%'.$query.'%';

        $products = Product::query()
            ->with('category')
            ->where(function ($builder) use ($like) {
                $builder->where('name', 'like', $like)
                    ->orWhere('sku', 'like', $like);
            })
            ->latest()
            ->limit(8)
            ->get();

        $orders = Order::query()
            ->with('user')
            ->where(function ($builder) use ($like) {
                $builder->where('order_number', 'like', $like)
                    ->orWhere('billing_full_name', 'like', $like)
                    ->orWhere('billing_email', 'like', $like)
                    ->orWhere('billing_phone', 'like', $like);
            })
            ->latest()
            ->limit(8)
            ->get();

        $customers = User::query()
            ->where('role', UserRole::Customer)
            ->where(function ($builder) use ($like) {
                $builder->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like);
            })
            ->latest()
            ->limit(8)
            ->get();

        $categories = Category::query()
            ->where(function ($builder) use ($like) {
                $builder->where('name', 'like', $like)
                    ->orWhere('slug', 'like', $like);
            })
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.search', compact(
            'query',
            'products',
            'orders',
            'customers',
            'categories',
        ));
    }
}
