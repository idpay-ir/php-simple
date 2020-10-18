<?php

require_once('variables.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $response = $_POST;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $response = $_GET;
}

if (empty($response['status']) ||
    empty($response['id']) ||
    empty($response['track_id']) ||
    empty($response['order_id'])) {

  return FALSE;
}

if ($response['status'] != 10) {
  print idpay_payment_get_message($response['status']);
}

// if $response['id'] was not in the database return FALSE

$inquiry = idpay_payment_get_inquiry($response);

if ($inquiry) {
  $verify = idpay_payment_verify($response);
}


/**
 * @param array $response
 * @return bool
 */
function idpay_payment_get_inquiry($response) {

  $header = array(
    'Content-Type: application/json',
    'X-API-KEY:' . APIKEY,
    'X-SANDBOX:' . SANDBOX,
  );

  $params = array(
    'id' => $response['id'],
    'order_id' => $response['order_id'],
  );

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, URL_INQUIRY);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

  $result = curl_exec($ch);
  curl_close($ch);

  $result = json_decode($result);

  if (empty($result) ||
      empty($result->status)) {

    print 'Exception message:';
    print '<pre>';
    print_r($result);
    print '</pre>';

    return FALSE;
  }

  if ($result->status == 10) {
    return TRUE;
  }

  print idpay_payment_get_message($result->status);

  return FALSE;
}


/**
 * @param array $response
 * @return bool
 */
function idpay_payment_verify($response) {

  $header = array(
    'Content-Type: application/json',
    'X-API-KEY:' . APIKEY,
    'X-SANDBOX:' . SANDBOX,
  );

  $params = array(
    'id' => $response['id'],
    'order_id' => $response['order_id'],
  );

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, URL_VERIFY);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

  $result = curl_exec($ch);
  curl_close($ch);

  $result = json_decode($result);

  if (empty($result) ||
      empty($result->status)) {

    print 'Exception message:';
    print '<pre>';
    print_r($result);
    print '</pre>';

    return FALSE;
  }

  print idpay_payment_get_message($result->status);

  print '<pre>';
  print_r($result);
  print '</pre>';
}

/**
 * @param int $status
 * @return string
 */
function idpay_payment_get_message($status) {

  switch ($status) {
    case 1:
      return 'پرداخت انجام نشده است';

    case 2:
      return 'پرداخت ناموفق بوده است';

    case 3:
      return 'خطا رخ داده است';

    case 10:
      return 'در انتظار تایید پرداخت';

    case 100:
      return 'پرداخت تایید شده است';

    case 101:
      return 'پرداخت قبلاً تایید شده است';

    default:
      return 'Error handeling';
  }
}
