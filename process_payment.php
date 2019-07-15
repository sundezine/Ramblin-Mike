<?php
require '../vendor/squareup/autoload.php';
require'../vendor/fpdf/fpdf.php';

$access_token = 'EAAAEPOnTTZ7hpetO-WEOl7sSKvYIrVVCrAbJjhTCTfQgYj7og9UnuafCqT7XssN';
# setup authorization
\SquareConnect\Configuration::getDefaultConfiguration()->setAccessToken($access_token);
# create an instance of the Transaction API class
$transactions_api = new \SquareConnect\Api\TransactionsApi();
$location_id = 'CBASEKbCR6IwgjAKnyxR6kbD8d8gAQ';
$nonce = $_POST['nonce'];

$request_body = array (
    "card_nonce" => $nonce,
# Monetary amounts are specified in the smallest unit of the applicable currency.
# This amount is in cents. It's also hard-coded for $1.00, which isn't very useful.
    "amount_money" => array (
        "amount" => (int) $_POST['amount'],
        "currency" => "USD"
    ),
# Every payment you process with the SDK must have a unique idempotency key.
# If you're unsure whether a particular payment succeeded, you can reattempt
# it with the same idempotency key without worrying about double charging
# the buyer.
    "idempotency_key" => uniqid()
);

try {
    $result = $transactions_api->charge($location_id,  $request_body);
    if($result['transaction']['id']){
        $transaction_id = $result['transaction']['id'];
        $result = $transactions_api->retrieveTransaction($location_id, $transaction_id);
        $result_clean = json_decode($result, true);
        $amount = $result_clean["transaction"]["tenders"][0]["amount_money"]["amount"];
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',10);
        $pdf->Ln();
        $pdf->Cell(40,10,"Meet & Greet Stress Management and Card Reading 6 Claymont Ct. South Palm Coast, FL 32137");
        $pdf->Ln();
        $pdf->Cell(40,10,"Hilton Garden Inn Palm Coast/Town Center");
        $pdf->Ln();
        $pdf->Cell(40,10,"Group code: YKM");
        $pdf->Ln();
        $pdf->Cell(40,10,"Amount: ".$amount);
        $pdf->Output();

    }
} catch (\SquareConnect\ApiException $e) {
    echo "Exception when calling TransactionApi->charge:";
    var_dump($e->getResponseBody());
}
?>
