<?php

namespace App\Services;

use App\Repositories\{
    TransactionRepository,
    MerchantProductRepository,
    ProductRepository,
    MerchantRepository
};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransactionService
{
    public function __construct(
        private TransactionRepository       $transactionRepository,
        private MerchantProductRepository   $merchantProductRepository,
        private ProductRepository           $productRepository,
        private MerchantRepository          $merchantRepository,
    ) {}

    /* ------------------------------------------------------------------  
     |  LIST & DETAIL
     |------------------------------------------------------------------*/
    public function getAll(array $fields = ['*'])
    {
        return $this->transactionRepository->getAll($fields);
    }

    public function getTransactionById(int $id, array $fields = ['*'])
    {
        $tx = $this->transactionRepository->getById($id, $fields);

        if (!$tx) {
            throw ValidationException::withMessages([
                'transaction_id' => ['Transaction not found.'],
            ]);
        }

        return $tx;
    }

    /* ------------------------------------------------------------------  
     |  LIST BY MERCHANT
     |------------------------------------------------------------------*/
    public function getTransactionsByMerchant(int $merchantId)
    {
        return $this->transactionRepository->getTransactionsByMerchant($merchantId);
    }

    /* ------------------------------------------------------------------  
     |  CREATE
     |------------------------------------------------------------------*/
    public function createTransaction(array $data)
    {
        return DB::transaction(function () use ($data) {

            /* ---------- validasi merchant ---------- */
            $merchant = $this->merchantRepository
                             ->getById($data['merchant_id'], ['id', 'keeper_id']);

            if (!$merchant) {
                throw ValidationException::withMessages([
                    'merchant_id' => ['Merchant not found.'],
                ]);
            }
if (Auth::id() !== $merchant->keeper_id) {
    throw ValidationException::withMessages([
        'authorization' => ['You are not the keeper of this merchant.'],
    ]);
}


          
            $items     = [];
            $subTotal  = 0;

            foreach ($data['products'] as $row) {

                $mp = $this->merchantProductRepository
                           ->getByMerchantAndProduct($data['merchant_id'], $row['product_id']);

                if (!$mp || $mp->stock < $row['quantity']) {
                    throw ValidationException::withMessages([
                        'stock' => ["Stock not enough for product {$row['product_id']}."],
                    ]);
                }

                $product = $this->productRepository
                                ->getById($row['product_id'], ['price']);

                if (!$product) {
                    throw ValidationException::withMessages([
                        'product_id' => ["Product {$row['product_id']} not found."],
                    ]);
                }

                $lineTotal = $row['quantity'] * $product->price;
                $subTotal += $lineTotal;

                $items[] = [
                    'product_id' => $row['product_id'],
                    'quantity'   => $row['quantity'],
                    'price'      => $product->price,
                    'sub_total'  => $lineTotal,
                ];

                /* update stok merchant */
                $this->merchantProductRepository->updateStock(
                    $data['merchant_id'],
                    $row['product_id'],
                    $mp->stock - $row['quantity']
                );
            }

            /* ---------- hitung total & simpan ---------- */
            $taxTotal   = $subTotal * 0.10;
            $grandTotal = $subTotal + $taxTotal;

            $tx = $this->transactionRepository->create([
    'name'        => $data['name'],
    'phone'       => $data['phone'],
    'merchant_id' => $data['merchant_id'],
    'sub_total'   => $subTotal,
    'tax_total'   => $taxTotal,      // ✅ tambahkan ini
    'grand_total' => $grandTotal,
]);


            $this->transactionRepository->createTransactionProducts($tx->id, $items);

            return $tx->fresh()->load('products');
        });
    }
}