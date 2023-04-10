<?php

require_once realpath(__DIR__ . '/..') . '/autoload.php';

use TimoPaul\ProcessingPartners\Client;
use TimoPaul\ProcessingPartners\Requests\GetPayment;
use TimoPaul\ProcessingPartners\Requests\SendPayment;

/**
 * Returns the default value for a POST parameter.
 *
 * @param string $key
 * @return string|null
 */
function getDefault(string $key): ?string
{
    return match ($key) {
        'token'     => 'YOUR_SANDBOX_ACCESS_TOKEN',
        'entityId'  => 'YOUR_SANDBOX_ENTITY_ID',
        'mode'      => 'test',
        default     => null,
    };
}

/**
 * Returns the value for a POST variable.
 *
 * @param string $key
 * @param bool|null $getDefault
 * @param int $filter
 * @return mixed|string|null
 */
function getPostValue(string $key, ?bool $getDefault = false, int $filter = FILTER_DEFAULT): mixed
{
    if (filter_has_var(INPUT_POST, $key)) {
        $value =  filter_input(INPUT_POST, $key, $filter);
        return $value ?: null;
    }
    if ($getDefault === true) {
        return getDefault($key);
    }
    return null;
}

function getClient(): Client
{
    $token      = getPostValue('token');
    $entityId   = getPostValue('entityId');
    $isLive     = 'live' === (string) getPostValue('mode');

    return new Client($token, $entityId, $isLive);
}

function getErrorOutput(Client $client): string
{
    return implode("\n", [
        'HTTP-Code: ' . $client->getHttpStatus(),
        'CURL-Error: ' . $client->getCurlError(),
        'CURL-Error-Nr: ' . $client->getCurlErrno(),
    ]);
}

function getResponseOutput(Client $client, $response): string
{
    if (null === $response) {
        return getErrorOutput($client);
    }

    $output = 'HTTP-Code: ' . $client->getHttpStatus();

    if ( ! is_array($response) || 0 < count($response)) {
        $output .= "\n\n" . print_r($response, true);
    }

    return $output;
}

function sendPayment(): string
{
    $paymentType = filter_input(INPUT_POST, 'sendPayment-paymentType');
    $brand = filter_input(INPUT_POST, 'sendPayment-paymentBrand');
    $cardNumber = filter_input(INPUT_POST, 'sendPayment-cardNumber');
    $amount = filter_input(INPUT_POST, 'sendPayment-amount');
    $currency = filter_input(INPUT_POST, 'sendPayment-currency');
    $expiryMonth = filter_input(INPUT_POST, 'sendPayment-expiryMonth');
    $expiryYear = filter_input(INPUT_POST, 'sendPayment-expiryYear');

    $client = getClient();
    $request = $client->generateRequest(SendPayment::class)
        ->addParameter(SendPayment::PARAMETER_PAYMENT_TYPE, $paymentType)
        ->addParameter(SendPayment::PARAMETER_PAYMENT_BRAND, $brand)
        ->addParameter(SendPayment::PARAMETER_CARD_NUMBER, $cardNumber)
        ->addParameter(SendPayment::PARAMETER_AMOUNT, $amount)
        ->addParameter(SendPayment::PARAMETER_CURRENCY, $currency)
        ->addParameter(SendPayment::PARAMETER_CARD_EXPIRY_MONTH, $expiryMonth)
        ->addParameter(SendPayment::PARAMETER_CARD_EXPIRY_YEAR, $expiryYear);

    return getResponseOutput($client, $client->sendRequest($request));
}


function getPayment(): string
{
    $paymentId = filter_input(INPUT_POST, 'getPayment-paymentId');

    $client = getClient();
    $request = $client->generateRequest(GetPayment::class)
        ->addParameter(GetPayment::PARAMETER_PAYMENT_ID, $paymentId);

    return getResponseOutput($client, $client->sendRequest($request));
}






function handleRequest(string $request): ?string
{
    if (getPostValue($request) && function_exists($request)) {
        if ( ! getPostValue('token')) {
            return 'Access Token missing!';
        }
        if ( ! getPostValue('entityId')) {
            return 'Entity ID missing!';
        }
        return call_user_func($request);
    }
    return 'Invalid request "' . $request . '"';
}

?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>ProcessingPartners PHP SDK - sandbox</title>
        <style type="text/css">
            * {
                font-family: verdanam, sans-serif;
            }
            h3 a {
                color: #49B382;
                text-decoration: none;
            }
            .container {
                max-width: 1200px;
                margin: auto;
                padding: 20px 0;
            }
            fieldset {
                margin-bottom: 20px;
            }
            fieldset pre {
                width: 100%;
                overflow-x: auto;
            }
            h4 {
                margin-top: 20px;
                margin-bottom: 0px;
            }
            pre {
                margin-top: 5px;
                margin-bottom: 5px;
            }
            a.reset {
                font-size: 80%;
                text-decoration: none;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>ProcessingPartners PHP SDK - sandbox</h1>
            <h3>Presented by <a href="https://timopaul.biz/" target="_blank">Timo Paul Dienstleistungen</a></h3>
            <form id="sdk" name="sdk" method="post">
                <fieldset>
                    <legend>Base configuration</legend>
                    <table>
                        <tr>
                            <td width="250">Access Token</td>
                            <td><input type="text" name="token" value="<?php echo getPostValue('token'); ?>" size="65"></td>
                        </tr>
                        <tr>
                            <td>Entity Id</td>
                            <td><input type="text" name="entityId" value="<?php echo getPostValue('entityId'); ?>" size="30"></td>
                        </tr>
                        <tr>
                            <td>Mode</td>
                            <td>
                                <select name="mode" style="width:265px;">
                                    <option value="live" <?php if (getPostValue('mode') == 'live') echo 'selected'; ?>>Live</option>
                                    <option value="test" <?php if (getPostValue('mode') == 'test') echo 'selected'; ?>>Test</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </fieldset>
                <fieldset>
                    <legend>Send an Initial Payment</legend>
                    <div>
                        <table>
                            <tr>
                                <td width="250">Payment type</td>
                                <td>
                                    <select type="select" name="sendPayment-paymentType">
                                        <?php foreach ([
                                            'PA' => 'Preauthorization' ,
                                            'DB' => 'Debit' ,
                                            'CD' => 'Credit' ,
                                            'CP' => 'Capture' ,
                                            'RV' => 'Reversal' ,
                                            'RF' => 'Refund' ,
                                        ] as $paymentType => $paymentTypeLabel) { ?>
                                            <option value="<?php echo $paymentType; ?>"
                                                <?php echo getPostValue('sendPayment-paymentType') == $paymentType ? ' selected="selected"' : '' ?>><?php echo $paymentTypeLabel; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Brand</td>
                                <td>
                                    <select type="select" name="sendPayment-paymentBrand">
                                        <?php foreach ([
                                                           'VISA',
                                                           'MASTER',
                                                           'AMEX',
                                                           'MAESTRO',
                                                           //'SEPA',
                                                           'VISADEBIT',
                                                       ] as $paymentBrand) { ?>
                                            <option value="<?php echo $paymentBrand; ?>"
                                                <?php echo getPostValue('sendPayment-paymentBrand') == $paymentBrand ? ' selected="selected"' : '' ?>><?php echo $paymentBrand; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Card number</td>
                                <td>
                                    <input type="text" name="sendPayment-cardNumber" value="<?php echo getPostValue('sendPayment-cardNumber'); ?>" size="20">
                                </td>
                            </tr>
                            <tr>
                                <td>Amount</td>
                                <td>
                                    <input type="text" name="sendPayment-amount" value="<?php echo getPostValue('sendPayment-amount'); ?>" size="5">
                                    <select type="select" name="sendPayment-currency">
                                        <?php foreach ([
                                            'EUR',
                                            'GBP',
                                            'USD',
                                        ] as $currency) { ?>
                                            <option value="<?php echo $currency; ?>"
                                                <?php echo getPostValue('sendPayment-currency') == $currency ? ' selected="selected"' : '' ?>><?php echo $currency; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Expiry</td>
                                <td>
                                    <select type="select" name="sendPayment-expiryMonth">
                                        <?php for ($month = 1; $month <= 12; $month++) { ?>
                                            <option value="<?php echo str_pad($month, 2, '0', STR_PAD_LEFT); ?>"
                                                <?php echo getPostValue('sendPayment-expiryMonth') == $month ? ' selected="selected"' : '' ?>><?php echo date('F', mktime(0, 0, 0, $month, 10)); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <select type="select" name="sendPayment-expiryYear">
                                        <?php for ($year = date('Y'); $year <= date('Y') + 10; $year++) { ?>
                                            <option value="<?php echo $year; ?>"
                                                <?php echo getPostValue('sendPayment-expiryYear') == $year ? ' selected="selected"' : '' ?>><?php echo $year; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <input type="submit" name="sendPayment" value="Send request"><br />
                                </td>
                            </tr>
                        </table>
                    </div>
                    <?php if ($result = handleRequest('sendPayment')) { ?>
                        <div class="result">
                            <h4>Result:</h4>
                            <pre><?php echo $result; ?></pre>
                        </div>
                    <?php } ?>
                </fieldset>
                <fieldset>
                    <legend>GetPayment</legend>
                    <div>
                        <table>
                            <tr>
                                <td width="250">Payment-ID</td>
                                <td><input type="text" name="getPayment-paymentId" value="<?php echo getPostValue('getPayment-paymentId'); ?>" size="30"></td>
                            </tr>
                        </table>
                        <input type="submit" name="getPayment" value="Send request">
                    </div>
                    <?php if ($result = handleRequest('getPayment')) { ?>
                        <div class="result">
                            <h4>Result:</h4>
                            <pre><?php echo $result; ?></pre>
                        </div>
                    <?php } ?>
                </fieldset>
            </form>
        </div>
    </body>
</html>