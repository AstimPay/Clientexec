<?php

require_once 'modules/admin/models/GatewayPlugin.php';
require_once 'plugins/gateways/astimpay/AstimPay.php';

class PluginAstimPay extends GatewayPlugin
{
    public function getVariables()
    {
        return [
            lang("Plugin Name") => [
                "type"        => "hidden",
                "description" => "",
                "value"       => "AstimPay"
            ],
            lang('Signup Name') => [
                'type'        => 'text',
                'description' => lang('Select the name to display in the signup process for this payment type. Example: eCheck or Credit Card.'),
                'value'       => 'AstimPay'
            ],
            lang("API KEY") => [
                "type"        => "text",
                "description" => "Enter your API KEY",
                "value"       => ""
            ],
            lang("API URL") => [
                "type"        => "text",
                "description" => "Enter your API URL",
                "value"       => ""
            ],
            lang("Exchange Rate") => [
                "type"        => "text",
                "description" => "Exchange Rate (1 USD = ? BDT)",
                "value"       => ""
            ]
        ];
    }

    public function singlePayment($params)
    {
        $apiKey = $params['plugin_astimpay_API KEY'];
        $apiBaseURL = $params['plugin_astimpay_API URL'];
        $astimPay = new AstimPay($apiKey, $apiBaseURL);

        $baseURL = rtrim(CE_Lib::getSoftwareURL(), '/') . '/';
        $callbackURL = $baseURL . "plugins/gateways/astimpay/callback.php";
        $cancelURL = $params['invoiceviewURLCancel'];


        $invoiceId = $params['invoiceNumber'];
        $amount = round($params["invoiceTotal"], 2);
        $firstname = $params['userFirstName'];
        $lastname = $params['userLastName'];
        $email = $params['userEmail'];
        $currencyCode = $params['userCurrency'];
        $exchangeRate = !empty($params['plugin_astimpay_Exchange Rate']) ? $params['plugin_astimpay_Exchange Rate'] : 1;

        if ($currencyCode !== 'BDT') {
            $amount *= $exchangeRate;
        }

        $requestData = [
            'full_name'    => "$firstname $lastname",
            'email'        => $email,
            'amount'       => $amount,
            'metadata'     => [
                'invoice_id' => $invoiceId,
                'currency'   => $currencyCode
            ],
            'redirect_url' => $callbackURL,
            'return_type'  => 'GET',
            'cancel_url'   => $cancelURL,
            'webhook_url'  => $callbackURL
        ];

        try {
            $paymentUrl = $astimPay->initPayment($requestData);
            header('Location:' . $paymentUrl);
            exit();
        } catch (Exception $e) {
            die("Initialization Error: " . $e->getMessage());
        }
    }

    public function credit($params)
    {
    }
}
