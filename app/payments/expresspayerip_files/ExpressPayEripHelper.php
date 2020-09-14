<?php
require_once(dirname(__FILE__) . '/ExpressPayEripLog.php');

class ExpressPayEripHelper
{
    //Вычисление цифровой подписи
    public function compute_signature($request_params, $secret_word, $token, $method = 'add_invoice')
    {
        $secret_word = trim($secret_word);
        $normalized_params = array_change_key_case($request_params, CASE_LOWER);
        $api_method = array(
            'add_invoice' => array(
                "serviceid",
                "accountno",
                "amount",
                "currency",
                "expiration",
                "info",
                "surname",
                "firstname",
                "patronymic",
                "city",
                "street",
                "house",
                "building",
                "apartment",
                "isnameeditable",
                "isaddresseditable",
                "isamounteditable",
                "emailnotification",
                "smsphone",
                "returntype",
                "returnurl",
                "failurl"
            ),
            'get_qr_code' => array(
                "invoiceid",
                "viewtype",
                "imagewidth",
                "imageheight"
            ),
            'add_invoice_return' => array(
                "accountno",
                "invoiceno"
            )
        );

        $result = $token;

        foreach ($api_method[$method] as $item)
            $result .= (isset($normalized_params[$item])) ? $normalized_params[$item] : '';

        $hash = strtoupper(hash_hmac('sha1', $result, $secret_word));

        return $hash;
    }

    // Проверка электронной подписи
    function computeSignatureNotif($json, $secretWord)
    {
        $hash = NULL;

        $secretWord = trim($secretWord);

        if (empty($secretWord))
            $hash = strtoupper(hash_hmac('sha1', $json, ""));
        else
            $hash = strtoupper(hash_hmac('sha1', $json, $secretWord));
        return $hash;
    }


    // Отправка POST запроса
    public function sendRequestPOST($url, $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    // Отправка GET запроса
    public function sendRequestGET($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

        //Получение Qr-кода
        public function getQrCode($ExpressPayInvoiceNo, $token, $secretWord)
        {
            $request_params_for_qr = array(
                "Token" => $token,
                "InvoiceId" => $ExpressPayInvoiceNo,
                'ViewType' => 'base64'
            );
            $request_params_for_qr["Signature"] = $this->compute_signature($request_params_for_qr, $secretWord, $token, 'get_qr_code');
    
            $request_params_for_qr  = http_build_query($request_params_for_qr);
            $response_qr = $this->sendRequestGET('https://api.express-pay.by/v1/qrcode/getqrcode/?' . $request_params_for_qr);
            $response_qr = json_decode($response_qr);
            $qr_code = $response_qr->QrCodeBody;
            return $qr_code;
        }
}
