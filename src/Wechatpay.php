<?php
/**
 * Created by PhpStorm.
 * User: ibenchu-024
 * Date: 2017/6/1
 * Time: 22:37
 */

namespace Notadd\Multipay;

use Omnipay\Omnipay;
use Illuminate\Container\Container;
use Notadd\Foundation\Setting\Contracts\SettingsRepository;
use Illuminate\Http\Request;
use Notadd\Multipay\Helper as Helper;
use Omnipay\WechatPay\Message\CreateOrderRequest;
use Omnipay\WechatPay\Message\RefundOrderRequest;

class Wechatpay
{
    protected $settings;
    protected $gateway;

    public function __construct(){
        $this->settings = Container::getInstance()->make(SettingsRepository::class);
    }

    /*
        public function getData()
        {
            $order = new CreateOrderRequest();
            //创建订单配置
            $data = array(
                'appid'            => $order->getAppId(),
                'mch_id'           => $order->getMchId(),
                'device_info'      => $order->getDeviceInfo(),
                'body'             => $order->getBody(),
                'detail'           => $order->getDetail(),
                'attach'           => $order->getAttach(),
                'out_trade_no'     => $order->getOutTradeNo(),
                'fee_type'         => $order->getFeeType(),
                'total_fee'        => $order->getTotalFee(),
                'spbill_create_ip' => $order->getSpbillCreateIp(),
                'time_start'       => $order->getTimeStart(),
                'time_expire'      => $order->getTimeExpire(),
                'goods_tag'        => $order->getGoodsTag(),
                'notify_url'       => $order->getNotifyUrl(),
                'trade_type'       => $order->getTradeType(),
                'limit_pay'        => $order->getLimitPay(),
                'openid'           => $order->getOpenId(),
                'nonce_str'        => md5(uniqid()),
            );
            $data = array_filter($data);

            $data['sign'] = Helper::sign($data, $order->getApiKey());

            return $data;
        }

        //退款订单配置
        public function getrefundData()
        {

            $refund = new RefundOrderRequest();

            $data = array (
                'appid'           => $refund->getAppId(),
                'mch_id'          => $refund->getMchId(),
                'device_info'     => $refund->getDeviceInfo(),
                'transaction_id'  => $refund->getTransactionId(),
                'out_trade_no'    => $refund->getOutTradeNo(),
                'out_refund_no'   => $refund->getOutRefundNo(),
                'total_fee'       => $refund->getTotalFee(),
                'refund_fee'      => $refund->getRefundFee(),
                'refund_fee_type' => $refund->getRefundType(),
                'op_user_id'      => $refund->getOpUserId() ?: $refund->getMchId(),
                'nonce_str'       => md5(uniqid()),
            );

            $data = array_filter($data);

            $data['sign'] = Helper::sign($data, $refund->getApiKey());

            return $data;
        }
    */
    /*
     * 获取支付网关
     */
    public function getGateWay($gatewayname){

        $this->gateway = Omnipay::create($gatewayname);

        return $this;
    }

    public function pay(Array $para)
    {

        //http://pay.ibenchu.xyz:8080/api/multipay/pay?driver=wechat&way=WechatPay_Native&appid=wx2dd40b5b1c24a960&mch_id=1235851702&body=Iphone8&total_fee=10&out_trade_no=201706091212121000&spbill_create_ip=36.45.175.53&notify_url=http://pay.ibenchu.xyz:8080&nonce_str=c3b570e1c8441c0ae1f435c3c4de8464

        $originPara = $para;

        $sign = Helper::getSign($para);

        $para2 = [
            'sign' => $sign
        ];

        $originPara = $originPara + $para2;

        $response = $this->gateway->purchase($originPara)->send();

        dd($response);
        //available methods
 //       $response->getData(); //For debug
//        $response->getAppOrderData(); //For WechatPay_App
//        $response->getJsOrderData(); //For WechatPay_Js
        $response->getCodeUrl(); //For Native Trade Type

    }

    //回调通知
    public function webNotify($nonce_str,$sign,$result_code,$openid,$trade_type,$bank_type,$total_fee,$cash_fee_type,$transaction_id,$out_trade_no,$time_end){
        if('return_code'=='SUCCESS'){
            $options=[
                'nonce_str'=>$nonce_str,
                'sign'=>$sign,
                'result_code'=>$result_code,
                'openid'=>$openid,
                'trade_type'=>$trade_type,
                'bank_type'=>$bank_type,
                'total_fee'=>$total_fee,
                'cash_fee_type'=>$cash_fee_type,
                'transaction_id'=>$transaction_id,
                'out_trade_no'=>$out_trade_no,
                'time_end'=>$time_end
            ];
        }
        $request = $this->gateway->completePurchase($options);
        if ( $request->isPaid()) {
            echo "success";
        } else {
            echo "支付失败";
        }
    }
    //查询
    public function query($transaction_id,$nonce_str,$sign){
        $options = [
            'transaction_id'=>$transaction_id,
            '$nonce_str'=>$nonce_str,
            'sign'=>$sign
        ];
        $response = $this->gateway->query($options)->send();
        $response->isSuccessful();
    }

    //退款
    public function refund(){
        $this->gateway->setCertPath($this->settings->get('wechat.certpath'));
        $this->gateway->setKeyPath($this->settings->get('wechat.keypath'));
        $response = $this->gateway->refund([
            'nonce_str'=>$this->getrefundData($data['nonce_str']),
            'transaction_id'=>$this->getrefundData($data['transaction_id']),
            'sign'=>$this->getrefundData($data['sign']),
            'out_refund_no'=>$this->getrefundData($data['out_refund_no']),
            'total_fee'=>$this->getrefundData($data['total_fee']),
            'refund_fee'=>$this->getrefundData($data['refund_fee'])
        ])->send();
        $response->isSuccessful();
    }

    //取消

    public function cancel($out_trade_no)
    {
        $response = $this->gateway->close([
            'out_trade_no' => $out_trade_no, //The merchant trade no
        ])->send();
        var_dump($response->isSuccessful());
        var_dump($response->getData());
    }

}