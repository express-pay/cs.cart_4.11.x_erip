<?php

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

/////////////status/////
//  C-выполнен
//  P-обработан
//  I-анулирован
//  F-неудача
//  О-открыт
//  N-незавершен
//  A-на удержании
//  B-отложены

require_once(dirname(__FILE__) . '/expresspayerip_files/ExpressPayEripHelper.php');
require_once(dirname(__FILE__) . '/expresspayerip_files/ExpressPayEripLog.php');

$logs = new ExpressPayEripLog();
$helper = new ExpressPayEripHelper();

if (defined('PAYMENT_NOTIFICATION')) {

    $logs->log_info('callback', 'PAYMENT_NOTIFICATION start');

    $pp_response = array();

    $order_id = !empty($_REQUEST['order_id']) ? (int)$_REQUEST['order_id'] : 0;

    $order_info = fn_get_order_info($order_id);

    if (empty($processor_data)) {
        $processor_data = fn_get_processor_data($order_info['payment_id']);
    }

    $payment_settings   = $processor_data['processor_params'];

    if ($mode == "success") {
        $logs->log_info('callback', 'dispatch=success');

        $logs->log_info('callback', 'Signatures match');
        $response = array();

        $total = fn_format_price_by_currency($order_info['total'], CART_PRIMARY_CURRENCY, "BYN");
        $amount = number_format($total, 2, '.', '');
        $response['order_status'] = 'O';
        $response['reason_text'] = "_Ждет оплаты в ЕРИП";
        $response['paid_amount'] = $amount;
        fn_change_order_status($order_id, 'O');

        fn_finish_payment($order_id, $response);
        fn_order_placement_routines('route', $order_id);
    } elseif (isset($mode) && $mode == 'fail' && isset($_REQUEST['order_id'])) {
        $logs->log_error('fail', 'Request result; result - fail');

        $order_id = $_REQUEST['order_id'];
        $pp_response = array();
        $pp_response['order_status'] = 'F';
        $pp_response['reason_text'] = "_Оплата отменена системой оплаты/пользователем";
        fn_change_order_status($order_id, 'F');
        fn_finish_payment($order_id, $pp_response);
        fn_order_placement_routines('route', $order_id);

        $logs->log_info('fail', 'status processing "Canceled"');
        exit;
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($mode) && $mode == 'notify') {
        $logs->log_info('notify', 'start func');
        $data = $_POST['Data'];

        $data = json_decode($data, true);
        $comma_separated = implode(",", $data);
        $logs->log_info('notify', 'received data from the server : Data - ' . $comma_separated);
        $signature = $_REQUEST['Signature'];

        $order_id = $data['AccountNo'];
        $order_info = fn_get_order_info($order_id);

        if (isset($data['AccountNo']) && isset($signature) && $payment_settings['useSignatureForNotif'] == 'Y') {

            $sign = $helper->computeSignatureNotif($_POST['Data'], $payment_settings['secretWordForNotif']); //Генерация подписи из полученных данных

            $logs->log_info('notify', 'calculated signature; signature - ' . $sign);
            if ($signature == $sign) //проверка на совпадение подписей
            {
                $logs->log_info('notify', 'Signatures match');
                $response = array();
                switch ($data['CmdType']) {
                    case 1:
                        $amount = $data['Amount'];
                        $response['order_status'] = 'P';
                        $response['reason_text'] = "_Оплачено";
                        $response['paid_amount'] = $amount;
                        fn_update_order_payment_info($order_id, $response);
                        fn_change_order_status($order_id, 'P');
                        fn_finish_payment($order_id, $response);
                        header("HTTP/1.0 200 OK");
                        echo ("success pay");
                        exit;
                    case 2:
                        $response['order_status'] = 'I';
                        $response['reason_text'] = "_Отменен системой оплаты";
                        fn_update_order_payment_info($order_id, $response);
                        fn_change_order_status($order_id, 'I');
                        fn_finish_payment($order_id, $response);
                        header("HTTP/1.0 200 OK");
                        echo ("success canceled");
                        exit;
                    case 3:
                        $logs->log_info('callbackRequest', 'изменение статуса');
                        header("HTTP/1.0 200 OK");
                        break;
                }

                if (isset($data['Status']) && $data['CmdType'] == 3) {
                    switch ($data['Status']) {
                        case 1: //Ожидает оплату
                            $response['order_status'] = 'O';
                            $response['reason_text'] = "_Ожидает оплату";
                            fn_update_order_payment_info($order_id, $response);
                            fn_change_order_status($order_id, 'O');
                            fn_finish_payment($order_id, $response);
                            header("HTTP/1.0 200 OK");
                            echo ("success Ожидает оплату");
                            exit;
                        case 2: //Просрочен
                            $response['order_status'] = 'A';
                            $response['reason_text'] = "_Просрочен";
                            fn_update_order_payment_info($order_id, $response);
                            fn_change_order_status($order_id, 'A');
                            fn_finish_payment($order_id, $response);
                            header("HTTP/1.0 200 OK");
                            echo ("success Просрочен");
                            exit;
                        case 3: //Оплачен
                            $amount = $data['Amount'];
                            $response['order_status'] = 'P';
                            $response['reason_text'] = "_Оплачен";
                            $response['paid_amount'] = $amount;
                            fn_update_order_payment_info($order_id, $response);
                            fn_change_order_status($order_id, 'P');
                            fn_finish_payment($order_id, $response);
                            header("HTTP/1.0 200 OK");
                            echo ("success Оплачен");
                            exit;
                        case 4: //Оплачен частично 
                            $amount = $data['Amount'];
                            $response['order_status'] = 'B';
                            $response['reason_text'] = "_Оплачен частично";
                            $response['paid_amount'] = $amount;
                            fn_update_order_payment_info($order_id, $response);
                            fn_change_order_status($order_id, 'B');
                            fn_finish_payment($order_id, $response);
                            header("HTTP/1.0 200 OK");
                            echo ("success Оплачен частично");
                            exit;
                        case 5: // Отменен
                            $response['order_status'] = 'I';
                            $response['reason_text'] = "-Отменен";
                            $response['paid_amount'] = $amount;
                            fn_update_order_payment_info($order_id, $response);
                            fn_change_order_status($order_id, 'I');
                            fn_finish_payment($order_id, $response);
                            header("HTTP/1.0 200 OK");
                            echo ("success Отменен");
                            exit;
                        default:
                            header("HTTP/1.0 200 OK");

                            echo ('FAILED | the notice is not processed');

                            $logs->log_error('callbackRequest', 'FAILED | the notice is not processed; Status - ' . $status);
                            exit;
                    }
                }
            } else {
                $logs->log_error('notify', 'signatures do not match; Received signature - ' . $signature . '; Calculated signature - ' . $sign);
                exit;
            }
            header("HTTP/1.0 200 OK");
            echo ('FAILED | the notice is not processed'); //Ошибка в параметрах
            exit;
        } elseif (isset($data['AccountNo'])) {
            switch ($data['CmdType']) {
                case 1:
                    $amount = $data['Amount'];
                    $response['order_status'] = 'P';
                    $response['reason_text'] = "_Оплачено";
                    $response['paid_amount'] = $amount;
                    fn_update_order_payment_info($order_id, $response);
                    fn_change_order_status($order_id, 'P');
                    fn_finish_payment($order_id, $response);
                    header("HTTP/1.0 200 OK");
                    echo ("success pay");
                    exit;
                case 2:
                    $response['order_status'] = 'I';
                    $response['reason_text'] = "_Отменен системой оплаты";
                    fn_update_order_payment_info($order_id, $response);
                    fn_change_order_status($order_id, 'I');
                    fn_finish_payment($order_id, $response);
                    header("HTTP/1.0 200 OK");
                    echo ("success canceled");
                    exit;
                case 3:
                    $logs->log_info('callbackRequest', 'изменение статуса');
                    header("HTTP/1.0 200 OK");
                    break;
            }

            if (isset($data['Status']) && $data['CmdType'] == 3) {
                switch ($data['Status']) {
                    case 1: //Ожидает оплату
                        $response['order_status'] = 'O';
                        $response['reason_text'] = "_Ожидает оплату";
                        fn_update_order_payment_info($order_id, $response);
                        fn_change_order_status($order_id, 'O');
                        fn_finish_payment($order_id, $response);
                        header("HTTP/1.0 200 OK");
                        echo ("success Ожидает оплату");
                        exit;
                    case 2: //Просрочен
                        $response['order_status'] = 'A';
                        $response['reason_text'] = "_Просрочен";
                        fn_update_order_payment_info($order_id, $response);
                        fn_change_order_status($order_id, 'A');
                        fn_finish_payment($order_id, $response);
                        header("HTTP/1.0 200 OK");
                        echo ("success Просрочен");
                        exit;
                    case 3: //Оплачен
                        $amount = $data['Amount'];
                        $response['order_status'] = 'P';
                        $response['reason_text'] = "_Оплачен";
                        $response['paid_amount'] = $amount;
                        fn_update_order_payment_info($order_id, $response);
                        fn_change_order_status($order_id, 'P');
                        fn_finish_payment($order_id, $response);
                        header("HTTP/1.0 200 OK");
                        echo ("success Оплачен");
                        exit;
                    case 4: //Оплачен частично 
                        $amount = $data['Amount'];
                        $response['order_status'] = 'B';
                        $response['reason_text'] = "_Оплачен частично";
                        $response['paid_amount'] = $amount;
                        fn_update_order_payment_info($order_id, $response);
                        fn_change_order_status($order_id, 'B');
                        fn_finish_payment($order_id, $response);
                        header("HTTP/1.0 200 OK");
                        echo ("success Оплачен частично");
                        exit;
                    case 5: // Отменен
                        $response['order_status'] = 'I';
                        $response['reason_text'] = "-Отменен";
                        $response['paid_amount'] = $amount;
                        fn_update_order_payment_info($order_id, $response);
                        fn_change_order_status($order_id, 'I');
                        fn_finish_payment($order_id, $response);
                        header("HTTP/1.0 200 OK");
                        echo ("success Отменен");
                        exit;
                    default:
                        header("HTTP/1.0 200 OK");

                        echo ('FAILED | the notice is not processed');

                        $logs->log_error('callbackRequest', 'FAILED | the notice is not processed; Status - ' . $status);
                        exit;
                }
            }
        }
    } else {
        $logs->log_error('callback', '$_SERVER["REQUEST_METHOD"] !== "POST"');
        exit;
    }
    exit;
} else {
    /** @var array $order_info */
    /** @var array $processor_data */

    $logs->log_info('checkout_form', 'initialization checkout_form');

    $payment_settings   = $processor_data['processor_params'];

    $amount             = fn_format_price_by_currency($order_info['total']);

    $total = fn_format_price_by_currency($order_info['total'], CART_PRIMARY_CURRENCY, "BYN");
    $amount = number_format($total, 2, '.', '');

    $success_url        = fn_url("payment_notification.success?payment=expresspayerip&order_id=" . $order_id, AREA, 'current');
    $fail_url           = fn_url("payment_notification.fail?payment=expresspayerip&order_id=" . $order_id, AREA, 'current');
    $res['url']         = ($payment_settings['mode'] == "test") ? "https://sandbox-api.express-pay.by" : "https://api.express-pay.by";
    $res['url']         = $res['url'] . "/v1/web_invoices";
    $info               = 'Оплата заказа номер ' . $order_id . ' в интернет-магазине ' . fn_url("");

    if ($payment_settings["isNameEdit"] == "Y") {
        $isNameEdit = 1;
    } else {
        $isNameEdit = 0;
    }
    if ($payment_settings["isAddressEdit"] == "Y") {
        $isAddressEdit = 1;
    } else {
        $isAddressEdit = 0;
    }
    if ($payment_settings["isAmountEdit"] == "Y") {
        $isAmountEdit = 1;
    } else {
        $isAmountEdit = 0;
    }

    $request = array(
        'ServiceId'         => $payment_settings['serviceId'],
        'AccountNo'         => ($order_id),
        'Amount'            => $amount,
        'Currency'          => 933,
        'ReturnType'         => "json",
        'ReturnUrl'          => '',
        'FailUrl'            => '',
        'Info'              => $info,
        'Surname'           => $order_info['lastname'],
        'FirstName'         => $order_info['firstname'],
        'ReturnUrl'         => $success_url,
        'FailUrl'           => $fail_url,
        'IsNameEditable'    => $isNameEdit,
        'IsAddressEditable' => $isAddressEdit,
        'IsAmountEditable'  => $isAmountEdit,
        "SmsPhone"          => preg_replace('/[^0-9]/', '', $order_info["b_phone"]),
    );
    $helper     = new ExpressPayEripHelper();
    $signature  = $helper->compute_signature($request, $payment_settings['secretWord'], $payment_settings['token'], 'add_invoice');
    $request['Signature'] = $signature;

    $logs->log_info('checkout_form', 'End checkout_form');
    $pp_response = array();
    $pp_response['order_status'] = 'O';
    $pp_response['reason_text'] = "_Начата обработка";
    fn_update_order_payment_info($order_id, $pp_response);

    $response_erip = $helper->sendRequestPOST($res['url'], $request);

    $response_erip = json_decode($response_erip, true);

    if (isset($response_erip['Errors'])) {
        $_SESSION['module'] = "erip";
        $_SESSION['status'] = "not ok";
        $pp_response = array();
        $pp_response['order_status'] = 'F';
        $pp_response['reason_text'] = "_Оплата отменена системой оплаты/пользователем";
        fn_change_order_status($order_id, 'F');
        fn_finish_payment($order_id, $pp_response);
        fn_order_placement_routines('route', $order_id);
    } else {
        $_SESSION['module'] = "erip";
        $_SESSION['status'] = "ok";
        $_SESSION['order_id'] = $order_id;
        $_SESSION['amount'] = $amount;
        $_SESSION['erip_path'] = $payment_settings["pathToErip"];

        if ($payment_settings["showQrCode"] == "Y") {
            $showQrCode = true;
        } else {
            $showQrCode = false;
        }

        if ($showQrCode) {
            $_SESSION['showQrCode'] = $showQrCode;
            $qr_code = $helper->getQrCode($response_erip['ExpressPayInvoiceNo'], $payment_settings['token'], $payment_settings['secretWord']);
            $qr_description = 'Отсканируйте QR-код для оплаты';
            $_SESSION['qr_code'] = $qr_code;
            $_SESSION['qr_description'] = $qr_description;
        } 
        $response = array();
        $response['order_status'] = 'O';
        $response['reason_text'] = "_Ждет оплаты в ЕРИП";
        $response['paid_amount'] = $amount;
        fn_change_order_status($order_id, 'O');

        fn_finish_payment($order_id, $response);
        fn_order_placement_routines('route', $order_id);
        exit;
    }
}
