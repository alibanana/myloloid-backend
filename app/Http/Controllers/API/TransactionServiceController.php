<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Validator;

use App\Customer;
use App\User;
use App\Product;
use App\Colour;
use App\Size;

use App\Delivery;

use App\Transaction;
use App\Transaction_Detail;
use App\Transfer;

class TransactionServiceController extends BaseController
{
    public function __construct()
    {
        set_time_limit(8000000);
    }

    // Get Transaction Data
    public function getTransactions()
    {
        $transactions = Transaction::orderBy('created_at')->get();

        $data = array();
        foreach($transactions as $transaction)
        {
            $transaction_details = $transaction->transaction_details;
            $total = 0;
            foreach ($transaction_details as $item)
            {
                $total = $total + ($item['quantity'] * $item->product['price']);
            }
            
            $newtime = strtotime($transaction->created_at);
            $date = date('d M, Y',$newtime);

            $temp = [
                'id' => $transaction->id,
                'customer_id' => $transaction->customer_id,
                'delivery_id' => $transaction->delivery_id,
                'status' => $transaction->status,
                'invoice_no' => $transaction->invoice_no,
                'created_at' => $transaction->created_at,
                'updated_at' => $transaction->updated_at,
                'date' => $date,
                'total' => $total,
                'customer' => $transaction->customer,
                'delivery' => $transaction->delivery
            ];

            if ($transaction->customer->user_id != null)
            {
                $temp['user'] = $transaction->customer->user;
            }

            array_push($data, $temp);
        }

        return $this->sendResponse($data, 'Transactions data retrieved successfully.');
    }


    public function getUserTransactions($user_id)
    {
        if (Customer::where('user_id', $user_id)->exists())
        {
            $customer = Customer::firstWhere('user_id', $user_id);            
        } else {
            return $this->sendError([
                'title' => 'Error, Orders Not Found!',
                'heading' => "Error - Orders not Found!!",
                'message' => "Oops, seems like you do not have any orders yet."
            ]);
        }

        $transactions = Transaction::where('customer_id', $customer->id)->orderBy('created_at')->get();

        $data = array();
        foreach($transactions as $transaction)
        {
            $transaction_details = $transaction->transaction_details;
            $total = 0;
            foreach ($transaction_details as $item)
            {
                $total = $total + ($item['quantity'] * $item->product['price']);
            }
            
            $newtime = strtotime($transaction->created_at);
            $date = date('d M, Y',$newtime);

            $temp = [
                'id' => $transaction->id,
                'customer_id' => $transaction->customer_id,
                'delivery_id' => $transaction->delivery_id,
                'status' => $transaction->status,
                'invoice_no' => $transaction->invoice_no,
                'created_at' => $transaction->created_at,
                'updated_at' => $transaction->updated_at,
                'date' => $date,
                'total' => $total,
                'customer' => $transaction->customer,
                'delivery' => $transaction->delivery
            ];

            if ($transaction->customer->user_id != null)
            {
                $temp['user'] = $transaction->customer->user;
            }

            array_push($data, $temp);
        }

        return $this->sendResponse($data, 'User transactions data retrieved successfully.');
    }


    // Storing customer
    public function storeCustomer(Request $request)
    {
        $input = $request->all();

        if ($request->has('c_user_id')){
            if (Customer::where('user_id', $input['c_user_id'])->exists()){
                $customer = Customer::firstWhere('user_id', $input['c_user_id']);

                if (($customer->user->whatsapp == null) and (request()->has('c_whatsapp'))){
                    $user = User::find($input['c_user_id']);
                    $user->whatsapp = $input['c_whatsapp'];
                    $user->save();
                    return $this->sendResponse($customer->toArray(), "Customer exists already, added Customer's whatsapp information.");
                }
                return $this->sendResponse($customer->toArray(), 'Customer exists already.');
            }
            $customer = new Customer([
                'user_id' => $input['c_user_id'],
            ]);
            $customer->save();
            return $this->sendResponse($customer->toArray(), 'Added User to Customer data successfully.');
        }

        // Request validation
        $validator = Validator::make($input, [
            'c_email' => 'required',
            'c_name' => 'required',
            'c_phone' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        $customer = new Customer([
            'name' => $input['c_name'],
            'email' => $input['c_email'],
            'phone' => $input['c_phone'],
        ]);

        if (request()->has('c_whatsapp')){
            $customer->whatsapp = $input['c_phone'];
        }

        $customer->save();
        
        return $this->sendResponse($customer->toArray(), 'New Customer created successfully.');
    }


    // Store new delivery address/data
    public function storeDelivery(Request $request)
    {
        $input = $request->all();

        // Request validation
        $validator = Validator::make($input, [
            'r_name' => 'required',
            'r_phone' => 'required',
            'r_provinsi' => 'required',
            'r_kabupaten' => 'required',
            'r_kecamatan' => 'required',
            'r_address' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        
        $delivery = new Delivery([
            'name' => $input['r_name'],
            'phone' => $input['r_phone'],
            'provinsi' => $input['r_provinsi'],
            'kabupaten' => $input['r_kabupaten'],
            'kecamatan' => $input['r_kecamatan'],
            'alamat' => $input['r_address'],
        ]);

        if ($request->has('r_notes'))
        {
            $delivery->notes = $input['r_notes'];
        }

        $delivery->save();

        return $this->sendResponse($delivery->toArray(), 'Delivery data created successfully.');
    }


    // Store transaction data
    public function storeTransaction(Request $request)
    {
        $input = $request->all();

        // Request validation
        $validator = Validator::make($input, [
            'customer_id' => 'required',
            'delivery_id' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        $transaction = new Transaction([
            'customer_id' => $input['customer_id'],
            'delivery_id' => $input['delivery_id'],
        ]);

        $transaction->invoice_no = Str::random(8);

        while (true)
        {
            if (Transaction::where('invoice_no', $transaction->invoice_no)->exists())
            {
                $transaction->invoice_no = Str::random(8);
            } else {
                break;
            }
        }

        $transaction->save();
        
        return $this->sendResponse($transaction->toArray(), 'Transaction created successfully.');
    }


    // Update transaction status
    public function updateTransactionStatus(Request $request, $id)
    {
        $input = $request->all();

        // Request validation
        $validator = Validator::make($input, [
            'status' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        $transaction = Transaction::find($id);

        if ($transaction->status != $input['status'])
        {
            $transaction->status = $input['status'];
            $transaction->save();
        }

        return $this->sendResponse($transaction->toArray(), "Transaction status updated successfully.");
    }


    // Store transaction_details data
    public function storeTransactionDetails(Request $request)
    {
        $input = $request->all();

        // Request validation
        $validator = Validator::make($input, [
            'cart' => 'array|required',
            'cart.*.id' => 'required',
            'cart.*.colour' => 'required',
            'cart.*.size' => 'required',
            'cart.*.quantity' => 'required',
            'transaction_id' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        $transaction_id = $input['transaction_id'];
        foreach ($input['cart'] as $item){
            $transaction_details = new Transaction_Detail([
                'transaction_id' => $transaction_id,
                'product_id' => $item['id'],
                'colour_id' => $item['colour'],
                'size_id' => $item['size'],
                'quantity' => $item['quantity']
            ]);
            $transaction_details->save();
        }

        $all_transaction_details = Transaction_Detail::where('transaction_id', $transaction_id)->get();

        return $this->sendResponse($all_transaction_details, 'Transaction details created successfully.');
    }


    // Store transfer data
    public function storeTransfer(Request $request)
    {
        $input = $request->all();
        
        // Request validation
        $validator = Validator::make($input, [
            'invoice_no' => 'required',
            'transfer_date' => 'required',
            'transfer_time' => 'required',
            'sender_name' => 'required',
            'sender_phone' => 'required',
            'sender_bank' => 'required',
            'sender_acc_no' => 'required|integer',
            'amount' => 'required|integer',
            'receiver_bank' => 'required',
            'receiver_acc_no' => 'required|integer',
            'image' => 'file|required'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }
        
        list($month, $day, $year) = explode('/', $input['transfer_date']);
        $date = $year.'-'.$month.'-'.$day;

        list($hour, $minutes) = explode(':', $input['transfer_time']);
        $time = $hour.':'.$minutes.':00';

        $transfer = new Transfer([
            'transfer_date' => $date,
            'transfer_time' => $time,
            'sender_name' => $input['sender_name'],
            'sender_phone' => $input['sender_phone'],
            'sender_bank' => $input['sender_bank'],
            'sender_acc_no' => $input['sender_acc_no'],
            'amount' => $input['amount'],
            'receiver_name' => $input['receiver_name'],
            'receiver_bank' => $input['receiver_bank'],
            'receiver_acc_no' => $input['receiver_acc_no'],
        ]);

        if(Transaction::where('invoice_no', $input['invoice_no'])->exists())
        {
            $transaction = Transaction::firstWhere('invoice_no', $input['invoice_no']);
            $transfer->transaction_id = $transaction->id;
            $image = $request->file('image');
        } else {
            return $this->sendError([
                'title' => 'Error, Invoice Not Found!',
                'heading' => "Error - Invoice doesn't exists!!",
                'message' => "Oops, seems like you inserted the wrong invoice number"
            ]);
        }

        $ext = $image->getClientOriginalExtension();
        
        while(true){
            $newName = rand(100000,1001238912).".".$ext;

            if (!file_exists('uploads/invoices/'.$newName)){
                $image->move('uploads/invoices', $newName);
                break;
            }
        }
        
        $transfer->file = $newName;

        if ($request->has('notes')){
            $transfer->notes = $input['notes'];
        }
        
        $transfer->save();
        
        $transaction->status = 'Waiting Confirmation';
        $transaction->save();
        
        return $this->sendResponse($transfer->toArray(), 'Transfer data uploaded successfully.');
    }


    // Show Transaction
    public function showTransaction($invoice_no)
    {
        $transaction = Transaction::firstWhere('invoice_no', $invoice_no);
        $customer = Customer::find($transaction->customer_id);
        $delivery = Delivery::find($transaction->delivery_id);
        $transaction_details = Transaction_Detail::where('transaction_id', $transaction->id)->get();

        if ($customer->user_id != null){
            $customer = User::find($customer->user_id);
        }

        $total = 0;

        $td_data = array();
        foreach ($transaction_details as $td)
        {
            $product = Product::find($td->product_id);
            $colour = Colour::find($td->colour_id);
            $size = Size::find($td->size_id);
            $temp = [
                'id' => $td->id,
                'transaction_id' => $td->transaction_id,
                'product_id' => $td->product_id,
                'colour_id' => $td->colour_id,
                'size_id' => $td->size_id,
                'quantity' => $td->quantity,
                'product' => $product,
                'colour' => $colour,
                'size' => $size
            ];
            array_push($td_data, $temp);

            $total = $total + ($td->quantity * $product->price);
        }
        
        $newtime = strtotime($transaction->created_at);
        $date = date('d M, Y',$newtime);

        $data = [
            'id'=> $transaction->id,
            'customer_id' => $transaction->customer_id,
            'delivery_id' => $transaction->delivery_id,
            'status' => $transaction->status,
            'invoice_no' => $transaction->invoice_no,
            'created_at' => $transaction->created_at,
            'date' => $date,
            'total' => $total,
            'customer' => $customer,
            'delivery' => $delivery,
            'transaction_details' => $td_data
        ];

        if (Transfer::where('transaction_id', $transaction->id)->exists()){
            $transfer = Transfer::firstWhere('transaction_id', $transaction->id);
            $data['transfer'] = $transfer;
        }

        return $this->sendResponse($data, 'Transaction data send successfully.');
    }
}
