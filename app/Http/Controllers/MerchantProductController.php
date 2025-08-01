<?php

namespace App\Http\Controllers;

use App\Http\Requests\MerchantProductRequest;
use Illuminate\Http\Request;
use App\Services\MerchantProductService;

class MerchantProductController extends Controller
{
    //
    private MerchantProductService $merchantProductService;

    public function __construct(MerchantProductService $merchantProductService)
    {
        $this->merchantProductService = $merchantProductService;
    }

    public function store(MerchantProductRequest $request, int $merchant)

    {
        $validated = $request->validated();
        $validated['merchant_id'] = $merchant;

        $merchantProduct = $this->merchantProductService->assignProductToMerchant($validated);

        return response()->json([
            'message' => 'Product assigned to merchant successfully.',
            'data' => $merchantProduct,
        ], 201);
    }

    public function update(MerchantProductRequest $request, int $merchantId, int $productId)
    {
        $validated = $request->validated();
        $merchantProduct = $this->merchantProductService->updateStock(
            $merchantId,
            $productId,
            $validated['stock'],
            $validated['warehouse_id']

        );

        return response()->json([
            'message' => 'Product stock updated successfully.',
            'data' => $merchantProduct,
        ]);
    }

    public function destroy(int $merchant, int $product)
    {
        $this->merchantProductService->removeProductFromMerchant($merchant, $product);

        return response()->json([
            'message' => 'Product detached from merchant successfully.',
        ]);
    }
}
