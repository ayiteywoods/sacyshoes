<?php

namespace App\Services;

use App\Models\Favorite;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;

class FavoriteService
{
    public function toggle(User $user, Product $product): bool
    {
        $favorite = Favorite::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($favorite) {
            $favorite->delete();

            return false;
        }

        Favorite::query()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        return true;
    }

    /**
     * @return list<int>
     */
    public function productIdsFor(?User $user): array
    {
        if (! $user) {
            return [];
        }

        return Favorite::query()
            ->where('user_id', $user->id)
            ->pluck('product_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function isFavorited(?User $user, Product $product): bool
    {
        if (! $user) {
            return false;
        }

        return Favorite::query()
            ->where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->exists();
    }

    /**
     * @return Collection<int, Product>
     */
    public function productsFor(User $user): Collection
    {
        return $user->favoriteProducts()
            ->with(['images', 'category'])
            ->orderByPivot('created_at', 'desc')
            ->get();
    }
}
