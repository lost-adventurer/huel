<?php

namespace app\controllers;

use Yii;
use app\models\Customers;
use app\models\User;
use app\models\Products;
use app\models\Orders;
use yii\rest\ActiveController;
use yii\web\Response;


class ApiController extends ActiveController
{
    public $api_key = '4e3ecc839f804cc164159316b2ed49b0';
    public $password = '0cbf43fc88f41aefec5f27be8b02afd2';
    public $hostname = 'technical-be-rafe.myshopify.com';

    public $modelClass = 'app\models\Customers';

    private function getCustomers(){
        // Get cURL resource
        $curl = curl_init();
// Set some options - we are passing in a useragent too here
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'https://'.$this->api_key.':'.$this->password.'@'.$this->hostname.'/admin/customers.json?limit=250',
            CURLOPT_USERAGENT => 'Codular Sample cURL Request'
        ));
// Send the request & save response to $resp
        $resp = curl_exec($curl);
// Close request to clear up some resources
        curl_close($curl);

        $response = json_decode($resp);

        //code for checking if external feed is broken

        return $response->customers;
    }

    private function getOrders(){
// Get cURL resource
        $curl = curl_init();
// Set some options - we are passing in a useragent too here
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'https://'.$this->api_key.':'.$this->password.'@'.$this->hostname.'/admin/orders.json?limit=250&status=any',
            CURLOPT_USERAGENT => 'Codular Sample cURL Request'
        ));
// Send the request & save response to $resp
        $resp = curl_exec($curl);
// Close request to clear up some resources
        curl_close($curl);

        $response = json_decode($resp);

        //code for checking if external feed is broken

        return $response->orders;
    }

    private function getProducts(){
// Get cURL resource
        $curl = curl_init();
// Set some options - we are passing in a useragent too here
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'https://'.$this->api_key.':'.$this->password.'@'.$this->hostname.'/admin/products.json?limit=250',
            CURLOPT_USERAGENT => 'Codular Sample cURL Request'
        ));
// Send the request & save response to $resp
        $resp = curl_exec($curl);
// Close request to clear up some resources
        curl_close($curl);

        $response = json_decode($resp);

        //code for checking if external feed is broken

        return $response->products;
    }

    public function actionPopulate(){
        $customers = $this->getCustomers();

        foreach($customers as $customer){
            $new_customer = new Customers;
            $new_customer->api_id = $customer->id;
            $new_customer->first_name = $customer->first_name;
            $new_customer->last_name = $customer->last_name;

            $exists = Customers::find()->where( [ 'api_id' => $customer->id ] )->exists();
            if(!$exists){
                $new_customer->save();
            }
        }

        $orders = $this->getOrders();

        foreach($orders as $order){
            $new_order = new Orders;
            $new_order->api_id = $order->id;
            $new_order->customer_id = $order->customer->id;
            $new_order->total_price = $order->total_price;

            $exists = Orders::find()->where( [ 'api_id' => $order->id ] )->exists();
            if(!$exists){
                $new_order->save();
            }
        }

        $products = $this->getProducts();

        foreach($products as $product){
            $new_product = new Products;
            $new_product->api_id = $product->id;
            $new_product->title = $product->title;

            $exists = Products::find()->where( [ 'api_id' => $product->id ] )->exists();
            if(!$exists){
                $new_product->save();
            }
        }

        exit('Import complete!');
    }


    public function actionAMeanCustomer($id){
        $orders = Orders::findAll(['customer_id'=>$id]);
        $order_count = count($orders);
        $order_total = 0;
        foreach($orders as $order){
            $order_total += $order->total_price;
        }

        echo 'Customer order total average: £';
        if($order_total > 0){
            exit($order_total / $order_count);
        }else{
            exit('0');
        }
    }

    public function actionAllTheMeanCustomers(){
        $all_customers = Customers::find()->all();
        $customer_count = count($all_customers);
        $customer_order_total = [];
        foreach($all_customers as $customer){
            $orders = Orders::findAll(['customer_id'=>$customer->api_id]);
            $order_total = 0;
            foreach($orders as $order){
                $order_total += $order->total_price;
            }
            $customer_order_total[$customer->api_id] = $order_total;
        }

        $grand_total = 0;

        foreach($customer_order_total as $customer => $total){
            $grand_total += $total;
        }

        echo 'All customers order total average: £';
        if($grand_total > 0){
            exit($grand_total / $customer_count);
        }else{
            exit('0');
        }
    }
}