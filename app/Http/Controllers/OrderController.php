<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Order;
use App\Cart;
use App\OrderProduct;


class OrderController extends Controller{


	public function index(){
		$orders = Order::all();

		return view("admin.order.index", ["orders" => $orders]);
	}



	public function update($id, Request $request){
		$order = Order::find($id);

		if ($request->isMethod('post')){



			if($order){

				$order->status = $request->input("status");

				$delete_ids = $request->input("delete-ids");

				if($delete_ids){
					$delete_ids = explode(", ", $delete_ids);
					DB::table('order_product')->whereIn('id', $delete_ids)->delete();
				}


				$order->save();
			}


			return redirect()->route("order.index");
		}

		return view("admin.order.update", ["model" => $order, "statuses" => Order::$statuses, "form_url" => "order.update"]);
	}


	public function create(Request $request){
		$cart = Cart::getCart($request->session()->getId());

		$order = new Order();
		$order->user_id = Auth::user()->id;
		$order->save();

		foreach($cart->products as $product){
			$orderProduct = new OrderProduct();
			$orderProduct->order_id = $order->id;
			$orderProduct->product_id = $product->id;
			$orderProduct->amount = 1;
			$orderProduct->save();
		}

		$cart->products()->sync([]);

		return redirect()->route("index");
	}



}