<?php

require_once realpath(__DIR__ . '/..') . '/autoload.php';

use TimoPaul\ProcessingPartners\Client;
use TimoPaul\ProcessingPartners\Exceptions\InvalidParameterException;
use TimoPaul\ProcessingPartners\Exceptions\MissingPropertyException;
use TimoPaul\ProcessingPartners\Exceptions\UnauthorizedAccessException;
use TimoPaul\ProcessingPartners\Requests\GetPayment;
use TimoPaul\ProcessingPartners\Requests\GetQuery;
use TimoPaul\ProcessingPartners\Requests\GetResultCodes;
use TimoPaul\ProcessingPartners\Requests\SendPayment;
use TimoPaul\ProcessingPartners\Requests\UpdatePayment;
use TimoPaul\ProcessingPartners\Response;
use TimoPaul\ProcessingPartners\Utils\PaymentTypes;
use TimoPaul\ProcessingPartners\Utils\ThreeDSecureRequestParameter;

/**
 * Returns the default value for a POST parameter.
 *
 * @param string $key
 * @return string|null
 */
function getDefault(string $key): ?string
{
    switch ($key) {
        case 'token';
            return 'YOUR_SANDBOX_ACCESS_TOKEN';
        case 'entityId';
            return 'YOUR_SANDBOX_ENTITY_ID';
        case 'mode';
            return 'test';
    }

    return null;
}

/**
 * Returns the value for a POST variable.
 *
 * @param string $key
 * @param bool|null $getDefault
 * @param int $filter
 * @return mixed|string|null
 */
function getPostValue(string $key, ?bool $getDefault = false, int $filter = FILTER_DEFAULT)
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

function getResponseOutput(Client $client, ?Response $response): string
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


/**
 * @throws InvalidParameterException
 * @throws MissingPropertyException
 * @throws UnauthorizedAccessException
 */
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
    /** @var SendPayment $request */
    $request = $client->generateRequest(SendPayment::class)
        ->addParameter(SendPayment::PARAMETER_PAYMENT_TYPE, $paymentType)
        ->addParameter(SendPayment::PARAMETER_PAYMENT_BRAND, $brand)
        ->addParameter(SendPayment::PARAMETER_CARD_NUMBER, $cardNumber)
        ->addParameter(SendPayment::PARAMETER_AMOUNT, $amount)
        ->addParameter(SendPayment::PARAMETER_CURRENCY, $currency)
        ->addParameter(SendPayment::PARAMETER_CARD_EXPIRY_MONTH, $expiryMonth)
        ->addParameter(SendPayment::PARAMETER_CARD_EXPIRY_YEAR, $expiryYear);

    if (filter_has_var(INPUT_POST, 'sendPayment-3d')) {
        $screenWidth = filter_input(INPUT_POST, 'sendPayment-screenWidth');
        $screenHeight = filter_input(INPUT_POST, 'sendPayment-screenHeight');
        $colorDepth = filter_input(INPUT_POST, 'sendPayment-colorDepth');

        $request->with3dSecure([
            ThreeDSecureRequestParameter::CUSTOMER_BROWSER_SCREEN_WIDTH => $screenWidth,
            ThreeDSecureRequestParameter::CUSTOMER_BROWSER_SCREEN_HEIGHT => $screenHeight,
            ThreeDSecureRequestParameter::CUSTOMER_BROWSER_SCREEN_COLOR_DEPTH => $colorDepth,
            ThreeDSecureRequestParameter::SHOPPER_RESULT_URL => $_SERVER['HTTP_REFERER'],
        ]);
    }
    
    return getResponseOutput($client, $client->sendRequest($request));
}


/**
 * @throws MissingPropertyException
 * @throws UnauthorizedAccessException
 * @throws InvalidParameterException
 */
function getPayment(): string
{
    $paymentId = filter_input(INPUT_POST, 'getPayment-paymentId');

    $client = getClient();
    /** @var GetPayment $request */
    $request = $client->generateRequest(GetPayment::class)
        ->addParameter(GetPayment::PARAMETER_PAYMENT_ID, $paymentId);

    return getResponseOutput($client, $client->sendRequest($request));
}


/**
 * @throws MissingPropertyException
 * @throws UnauthorizedAccessException
 * @throws InvalidParameterException
 */
function getQuery(): string
{
    $query = filter_input(INPUT_POST, 'getQuery-query');

    $client = getClient();
    /** @var GetQuery $request */
    $request = $client->generateRequest(GetQuery::class)
        ->addParameter(GetQuery::PARAMETER_QUERY, $query);

    return getResponseOutput($client, $client->sendRequest($request));
}


/**
 * @throws UnauthorizedAccessException
 * @throws MissingPropertyException
 */
function getResultCodes(): string
{
    $client = getClient();
    /** @var GetResultCodes $request */
    $request = $client->generateRequest(GetResultCodes::class);

    return getResponseOutput($client, $client->sendRequest($request));
}


/**
 * @throws InvalidParameterException
 * @throws MissingPropertyException
 * @throws UnauthorizedAccessException
 */
function updatePayment(): string
{
    $referencedPaymentId = filter_input(INPUT_POST, 'updatePayment-referencedPaymentId');
    $paymentType = filter_input(INPUT_POST, 'updatePayment-paymentType');
    $amount = filter_input(INPUT_POST, 'updatePayment-amount');
    $currency = filter_input(INPUT_POST, 'updatePayment-currency');

    $client = getClient();
    /** @var UpdatePayment $request */
    $request = $client->generateRequest(UpdatePayment::class)
        ->addParameter(UpdatePayment::PARAMETER_REFERENCED_PAYMENT_ID, $referencedPaymentId)
        ->addParameter(UpdatePayment::PARAMETER_PAYMENT_TYPE, $paymentType)
        ->addParameter(UpdatePayment::PARAMETER_AMOUNT, $amount)
        ->addParameter(UpdatePayment::PARAMETER_CURRENCY, $currency);

    return getResponseOutput($client, $client->sendRequest($request));
}





function handleRequest(string $request): ?string
{
    if (getPostValue($request)) {
        if ( ! function_exists($request)) {
            return 'Invalid request "' . $request . '"';
        }
        if ( ! getPostValue('token')) {
            return 'Access Token missing!';
        }
        if ( ! getPostValue('entityId')) {
            return 'Entity ID missing!';
        }
        return call_user_func($request);
    }
    return null;
}

?>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>ProcessingPartners PHP SDK - sandbox</title>
        <style>
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
                margin-bottom: 0;
            }
            pre {
                margin-top: 5px;
                margin-bottom: 5px;
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
                            <td><label for="token">Access Token</label></td>
                            <td><input type="text" name="token" id="token" value="<?php echo getPostValue('token'); ?>" size="65"></td>
                        </tr>
                        <tr>
                            <td><label for="entityId">Entity Id</label></td>
                            <td><input type="text" name="entityId" id="entityId" value="<?php echo getPostValue('entityId'); ?>" size="30"></td>
                        </tr>
                        <tr>
                            <td><label for="mode">Mode</label></td>
                            <td>
                                <select name="mode" id="mode" style="width:265px;">
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
                                <td><label for="sendPayment-paymentType">Payment type</label></td>
                                <td>
                                    <select name="sendPayment-paymentType" id="sendPayment-paymentType">
                                        <?php foreach ([
                                            PaymentTypes::PREAUTHORIZATION => 'Preauthorization' ,
                                            PaymentTypes::DEBIT => 'Debit' ,
                                            PaymentTypes::CREDIT => 'Credit' ,
                                            PaymentTypes::CAPTURE => 'Capture' ,
                                        ] as $paymentType => $paymentTypeLabel) { ?>
                                            <option value="<?php echo $paymentType; ?>"
                                                <?php echo getPostValue('sendPayment-paymentType') == $paymentType ? ' selected="selected"' : '' ?>><?php echo $paymentTypeLabel; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="sendPayment-paymentBrand">Brand</label></td>
                                <td>
                                    <select name="sendPayment-paymentBrand" id="sendPayment-paymentBrand">
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
                                <td><label for="sendPayment-cardNumber">Card number</label></td>
                                <td>
                                    <input type="text" name="sendPayment-cardNumber" id="sendPayment-cardNumber" value="<?php echo getPostValue('sendPayment-cardNumber'); ?>" size="20">
                                </td>
                            </tr>
                            <tr>
                                <td><label for="sendPayment-amount">Amount</label><label for="sendPayment-currency"></label></td>
                                <td>
                                    <input type="text" name="sendPayment-amount" id="sendPayment-amount" value="<?php echo getPostValue('sendPayment-amount'); ?>" size="5">
                                    <select name="sendPayment-currency" id="sendPayment-currency">
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
                                <td><label for="sendPayment-expiryMonth">Expiry</label><label for="sendPayment-expiryYear"></label></td>
                                <td>
                                    <select name="sendPayment-expiryMonth" id="sendPayment-expiryMonth">
                                        <?php for ($month = 1; $month <= 12; $month++) { ?>
                                            <option value="<?php echo str_pad($month, 2, '0', STR_PAD_LEFT); ?>"
                                                <?php echo getPostValue('sendPayment-expiryMonth') == $month ? ' selected="selected"' : '' ?>><?php echo date('F', mktime(0, 0, 0, $month, 10)); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <select name="sendPayment-expiryYear" id="sendPayment-expiryYear">
                                        <?php for ($year = date('Y'); $year <= date('Y') + 10; $year++) { ?>
                                            <option value="<?php echo $year; ?>"
                                                <?php echo getPostValue('sendPayment-expiryYear') == $year ? ' selected="selected"' : '' ?>><?php echo $year; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>
                                    <?php
                                        $checked = filter_has_var(INPUT_POST, 'sendPayment-3d');
                                    ?>
                                    <input type="checkbox" name="sendPayment-3d" id="sendPayment-3d"
                                           value="enabled"<?= $checked ? ' checked' : '' ?>>
                                    <label for="sendPayment-3d">Enable 3D Secure</label>
                                    <input type="hidden" name="sendPayment-screenWidth">
                                    <input type="hidden" name="sendPayment-screenHeight">
                                    <input type="hidden" name="sendPayment-colorDepth">
                                    <script>
                                        window.onload = function () {
                                            const elements = document.getElementById('sdk').elements;
                                            elements['sendPayment-screenWidth'].value = screen.width;
                                            elements['sendPayment-screenHeight'].value = screen.height;
                                            elements['sendPayment-colorDepth'].value = screen.colorDepth;
                                        }
                                    </script>

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
                    <legend>UpdatePayment</legend>
                    <div>
                        <table>
                            <tr>
                                <td><label for="updatePayment-referencedPaymentId">Payment-ID</label></td>
                                <td><input type="text" name="updatePayment-referencedPaymentId" id="updatePayment-referencedPaymentId" value="<?php echo getPostValue('updatePayment-referencedPaymentId'); ?>" size="30"></td>
                            </tr>
                            <tr>
                                <td><label for="updatePayment-paymentType">Payment type</label></td>
                                <td>
                                    <select name="updatePayment-paymentType" id="updatePayment-paymentType">
                                        <?php foreach ([
                                            PaymentTypes::REFUND => 'Refund' ,
                                            PaymentTypes::REVERSAL => 'Reversal' ,
                                        ] as $paymentType => $paymentTypeLabel) { ?>
                                            <option value="<?php echo $paymentType; ?>"
                                                <?php echo getPostValue('updatePayment-paymentType') == $paymentType ? ' selected="selected"' : '' ?>><?php echo $paymentTypeLabel; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><label for="updatePayment-amount">Amount</label><label for="updatePayment-currency"></label></td>
                                <td>
                                    <input type="text" name="updatePayment-amount" id="updatePayment-amount" value="<?php echo getPostValue('updatePayment-amount'); ?>" size="5">
                                    <select name="updatePayment-currency" id="updatePayment-currency">
                                        <?php foreach ([
                                                'EUR',
                                                'GBP',
                                                'USD',
                                            ] as $currency) { ?>
                                            <option value="<?php echo $currency; ?>"
                                                <?php echo getPostValue('updatePayment-currency') == $currency ? ' selected="selected"' : '' ?>><?php echo $currency; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        <input type="submit" name="updatePayment" value="Send request">
                    </div>
                    <?php if ($result = handleRequest('updatePayment')) { ?>
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
                                <td><label for="getPayment-paymentId">Payment-ID</label></td>
                                <td><input type="text" name="getPayment-paymentId" id="getPayment-paymentId" value="<?php echo getPostValue('getPayment-paymentId'); ?>" size="30"></td>
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
                <fieldset>
                    <legend>GetQuery</legend>
                    <div>
                        <table>
                            <tr>
                                <td><label for="getQuery-query">Query</label></td>
                                <td><input type="text" name="getQuery-query" id="getQuery-query" value="<?php echo getPostValue('getQuery-query'); ?>" size="30"></td>
                            </tr>
                        </table>
                        <input type="submit" name="getQuery" value="Send request">
                    </div>
                    <?php if ($result = handleRequest('getQuery')) { ?>
                        <div class="result">
                            <h4>Result:</h4>
                            <pre><?php echo $result; ?></pre>
                        </div>
                    <?php } ?>
                </fieldset>


                <fieldset>
                    <legend>GetResultcodes</legend>
                    <div>
                        <input type="submit" name="getResultCodes" value="Send request">
                    </div>
                    <?php if ($result = handleRequest('getResultCodes')) { ?>
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