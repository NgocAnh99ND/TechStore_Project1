<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddToCartRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function cartList()
    {
        $totalAmount = 0;
        $unifiedCart = [];
        $sessionCart = session('cart');


        if (Auth::check()) {
            $dbCart = Cart::with(['items.productVariant.product'])
                ->where('user_id', Auth::id())
                ->first();

            if ($dbCart) {
                foreach ($dbCart->items as $item) {
                    $product = $item->productVariant->product;
                    $productVariant = $item->productVariant;

                    $unifiedCart[$productVariant->id] = [
                        'product_variant_id' => $productVariant->id,
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'price' => $productVariant->price,
                        'quantity' => $item->quantity,
                        'color' => $productVariant->color->name,
                        'capacity' => $productVariant->capacity->name,
                        'image' => $productVariant->image
                    ];

                    $totalAmount += $item->quantity * ($product->price_sale ?? $product->price);
                }
                if (!empty($sessionCart)) {

                    foreach ($sessionCart as $item) {

                        $cartItem = CartItem::query()->where([
                            'cart_id' => $dbCart->id,
                            'product_variant_id' => $item['product_variant_id']
                        ])->first();
                        if ($cartItem) {
                            $newQuantity = $cartItem->quantity + $item['quantity'];
                            $cartItem->update(['quantity' => $newQuantity]);

                            if (isset($unifiedCart[$item['product_variant_id']])) {
                                $unifiedCart[$item['product_variant_id']]['quantity'] = $newQuantity;
                            }
                        } else {
                            $newCartItem = CartItem::query()->create([
                                'cart_id' => $dbCart->id,
                                'product_variant_id' => $item['product_variant_id'],
                                'quantity' => $item['quantity'],
                                'price' => $item['price']
                            ]);

                            $unifiedCart[$item['product_variant_id']] = [
                                'product_variant_id' => $newCartItem->product_variant_id,
                                'quantity' => $newCartItem->quantity,
                                'price' => $newCartItem->price,
                            ];
                        }

                        // Tính tổng giá
                        $totalAmount = $item['quantity'] * $item['price'];
                    }
                    // Xóa session sau khi đã lưu vào db
                    session()->forget('cart');
                }
            }
        } else {
            if (!empty($sessionCart)) {
                $unifiedCart = $sessionCart;

                foreach ($sessionCart as $item) {
                    $totalAmount += $item['quantity'] * $item['price'];
                }
            } else {
                return view('client.cart', [
                    'unifiedCart' => [],
                    'totalAmount' => 0,
                    'message' => 'No items in the cart'
                ]);
            }
        }

        return view('client.cart', compact('unifiedCart', 'totalAmount'));

    }


    public function addToCart(AddToCartRequest $request)
    {
        try {
            DB::beginTransaction();

            $product = Product::query()->findOrFail($request->product_id);
            $productVariant = ProductVariant::query()
                ->with(['color', 'capacity'])
                ->where([
                    'product_id' => $request->product_id,
                    'product_capacity_id' => $request->product_capacity_id,
                    'product_color_id' => $request->product_color_id,
                ])
                ->firstOrFail();

            $quantity = (int) $request->input('quantity', 0);

            $stock_quantity = $productVariant->quantity;

            if (Auth::check()) {
                $cart = Cart::query()->firstOrCreate([
                    'user_id' => Auth::id()
                ]);

                $cartItem = CartItem::query()->where([
                    'cart_id' => $cart->id,
                    'product_variant_id' => $productVariant->id
                ])->first();

                if ($cartItem) {
                    $newQuantity = $cartItem->quantity + $quantity;

                    if ($newQuantity > $stock_quantity) {
                        return back()->withErrors(['quantity' => 'Quantity exceeds inventory.']);
                    }

                    $cartItem->update(['quantity' => $newQuantity]);
                } else {
                    if ($quantity > $stock_quantity) {
                        return back()->withErrors(['quantity' => 'Quantity exceeds inventory.']);
                    }
                    CartItem::query()->create([
                        'cart_id' => $cart->id,
                        'product_variant_id' => $productVariant->id,
                        'quantity' => $quantity,
                        'price' => $productVariant->price
                    ]);
                }
            } else {
                $cart = session()->get('cart', []);
                $cartItemKey = $productVariant->id;

                if (isset($cart[$cartItemKey])) {
                    $newQuantity = $cart[$cartItemKey]['quantity'] + $quantity;

                    if ($newQuantity > $stock_quantity) {
                        return back()->withErrors(['quantity' => 'Quantity exceeds inventory..']);
                    }


                    $cart[$cartItemKey]['quantity'] = $newQuantity;
                } else {
                    if ($quantity > $stock_quantity) {
                        return back()->withErrors(['quantity' => 'Quantity exceeds inventory..']);
                    }

                    $cart[$cartItemKey] = [
                        'product_variant_id' => $productVariant->id,
                        'product_id' => $product->id,
                        'name' => $product->name,
                        'price' => $productVariant->price,
                        'quantity' => $quantity,
                        'color' => $productVariant->color->name,
                        'capacity' => $productVariant->capacity->name,
                        'image' => $productVariant->image
                    ];
                }
                session()->put('cart', $cart);
            }

            DB::commit();

        return redirect()->route('cart.list');

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'An error occurred, please try again.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteCart(Request $request)
    {
        $id = $request->input('deleteId');
        try {
            if (Auth::check()) {
                $cartItem = CartItem::whereHas('cart', function ($query) {
                    $query->where('user_id', Auth::id());
                })->where('product_variant_id', $id)->first();

                if ($cartItem) {
                    $cartItem->delete();
                }
            } else {

                $cart = session()->get('cart', []);

                if (isset($cart[$id])) {
                    unset($cart[$id]);
                    session()->put('cart', $cart);
                }
            }

            return redirect()->back()->with('success', 'The product has been removed from the cart..');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while removing the product from the cart.');
        }
    }

    public function updateQuantity(Request $request)
    {
        try {
            $productVariantId = $request->product_variant_id;
            $quantity = $request->quantity;


            if (!$productVariantId || $quantity < 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid data.'
                ], 400);
            }

            $productVariant = ProductVariant::find($productVariantId);
            if (!$productVariant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product does not exist.'
                ], 404);
            }

            if ($quantity > $productVariant->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Quantity requested exceeds quantity in stock.'
                ], 404);
            }

            if (Auth::check()) {
                $cartItem = CartItem::whereHas('cart', function ($query) {
                    $query->where('user_id', Auth::id());
                })->where('product_variant_id', $productVariantId)->first();

                if (!$cartItem) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Product does not exist in cart.'
                    ], 404);
                }

                $cartItem->update(['quantity' => $quantity]);

                $total = $this->calculateTotalForCartItem($cartItem);

            } else {
                $cart = session()->get('cart', []);
                if (isset($cart[$productVariantId])) {
                    $cart[$productVariantId]['quantity'] = $quantity;
                    session()->put('cart', $cart);

                    $total = $this->calculateTotal($cart[$productVariantId]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Product does not exist in cart.'
                    ], 404);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Update quantity successfully.',
                'total' => $total,
                'quantity' => $quantity
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }



    private function calculateTotal($item)
    {
        return number_format($item['price'] * $item['quantity'], 0, ',', '.');
    }

    private function calculateTotalForCartItem($item)
    {
        return number_format($item['price'] * $item['quantity'], 0, ',', '.');
    }

}
