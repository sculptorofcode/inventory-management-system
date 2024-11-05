<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Order - <?= APP_NAME ?></title>
    <link rel="icon" type="image/x-icon" href="<?= SITE_URL ?>/assets/images/logo/transparent/favicon.png"/>
</head>
<body style="font-family: 'Space Grotesk', sans-serif;background-color: #f4f4f4;">

<div style="background-color: #fff; padding: 10px 40px; margin: 10px auto;">
    <header style="border-bottom: 2px solid #fff; padding-bottom: 10px; margin-bottom: 10px;">
        <h1 style="font-size: 60px; color: #00a859; text-transform: uppercase; text-align: right;margin-bottom: 10px"><?= $print_type ?></h1>
        <div style="float: right;">
            <p style="margin: 0; font-size: 18px;"><strong><?= $print_type ?> Number :</strong>
                #<?= $order['inv_number'] ?></p>
            <p style="margin: 0; font-size: 18px;"><strong>Due Date
                    :</strong> <?= date('d M, Y', strtotime('+30 days', strtotime($order['order_date']))); ?></p>
            <p style="margin: 0; font-size: 18px;"><strong><?= $print_type ?> Date
                    :</strong> <?= date('d M, Y', strtotime($order['order_date'])) ?></p>
        </div>
        <div style="clear: both;"></div>
    </header>

    <section style="margin-bottom: 20px;">
        <p style="margin: 0; font-size: 18px;"><?= $print_type ?> to:</p>
        <h2 style="font-size: 24px; margin: 5px 0; color: #00a859;"><?= $customer['full_name'] ?></h2>
        <p style="margin: 5px 0; font-size: 18px;"><?= $customer['street_address'] ?><?= !empty($customer['city']) ? ', ' . $customer['city'] : '' ?><?= !empty($customer['state_province']) ? ', <br>' . $customer['state_province'] : '' ?><?= !empty($customer['postal_code']) ? ', ' . $customer['postal_code'] : '' ?></p>
    </section>

    <section style="margin-bottom: 20px;">
        <table style="width: 100%; border-collapse: collapse; border-spacing: 5px;">
            <thead>
            <tr style="box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);">
                <th style="padding: 5px; text-align: left; background-color: #7a93ae; font-size: 16px; font-weight: bold; color: black;border: 2px solid #ddd;">
                    Product Name
                </th>
                <th style="padding: 5px; text-align: center; background-color: #7a93ae; font-size: 16px; font-weight: bold; color: black;border: 2px solid #ddd;">
                    QUANTITY
                </th>
                <th style="padding: 5px; text-align: right; background-color: #7a93ae; font-size: 16px; font-weight: bold; color: black;border: 2px solid #ddd;">
                    PRICE
                </th>
                <th style="padding: 5px; text-align: right; background-color: #7a93ae; font-size: 16px; font-weight: bold; color: black;border: 2px solid #ddd;">
                    SUB TOTAL
                </th>
                <th style="padding: 5px; text-align: right; background-color: #7a93ae; font-size: 16px; font-weight: bold; color: black;border: 2px solid #ddd;">
                    GST
                </th>
                <th style="padding: 5px; text-align: right; background-color: #7a93ae; font-size: 16px; font-weight: bold; color: black;border: 2px solid #ddd;">
                    HSN CODE
                </th>
                <th style="padding: 5px; text-align: right; background-color: #7a93ae; font-size: 16px; font-weight: bold; color: black;border: 2px solid #ddd;">
                    AMOUNT
                </th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($order_details as $order_detail) { ?>
                <tr style="background-color: #f0f0f0;">
                    <td style="border: 2px solid #ddd; padding: 5px; font-size: 14px;"><?= html_entity_decode($order_detail['product_name']); ?></td>
                    <td style="border: 2px solid #ddd; padding: 5px; text-align: center; font-size: 14px;"><?= $order_detail['quantity'] ?></td>
                    <td style="border: 2px solid #ddd; padding: 5px; text-align: right; font-size: 14px;"><?= rupee($order_detail['sale_price'],2) ?></td>
                    <td style="border: 2px solid #ddd; padding: 5px; text-align: right; font-size: 14px;"><?= rupee($order_detail['sub_total'],2) ?></td>
                    <td style="border: 2px solid #ddd; padding: 5px; text-align: right; font-size: 14px;"><?= rupee($order_detail['gst_amount'],2) ?> <br><span style="text-wrap: nowrap;">(<?= $order_detail['gst_type'] == 1 ? 'CGST/SGST' : 'IGST' ?> - <?= round($order_detail['gst_rate'],2) ?>%)</span></td>
                    <td style="border: 2px solid #ddd; padding: 5px; text-align: right; font-size: 14px;"><?= !empty($order_detail['hsn_code']) ? $order_detail['hsn_code'] : '' ?></td>
                    <td style="border: 2px solid #ddd; padding: 5px; text-align: right; font-size: 14px;"><?= rupee($order_detail['total'],2) ?></td>
                </tr>
            <?php }
            $colspan = 6;
            ?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="<?= $colspan ?>" style="border: 2px solid #ddd; padding: 5px; text-align: right; font-size: 14px;">
                    SUBTOTAL
                </td>
                <td style="border: 2px solid #ddd; padding: 5px; text-align: right; font-size: 14px;">
                    <?= rupee(ceil($order['total_cost_price'] + $order['total_gst']),2) ?>
                </td>
            </tr>
            <tr>
                <td colspan="<?= $colspan ?>" style="border: 2px solid #ddd; padding: 5px; text-align: right; font-size: 14px;">
                    DISCOUNT
                </td>
                <td style="border: 2px solid #ddd; padding: 5px; text-align: right; font-size: 14px;">
                    <?= rupee($order['discount'],2) ?>
                </td>
            </tr>
            <tr>
                <td colspan="<?= $colspan ?>"
                    style="border: 2px solid #ddd; padding: 5px; text-align: right; font-size: 14px; font-weight: bold;">
                    TOTAL
                </td>
                <td style="border: 2px solid #ddd; padding: 5px; text-align: right; font-size: 14px; font-weight: bold;">
                    <?= rupee($order['total_amount'],2) ?>
                </td>
            </tr>
            <tr>
                <td colspan="<?= $colspan ?>"
                    style="border: 2px solid #ddd; padding: 5px; text-align: right; font-size: 14px; font-weight: bold;">
                    TOTAL PAID
                </td>
                <td style="border: 2px solid #ddd; padding: 5px; text-align: right; font-size: 14px; font-weight: bold;">
                    <?= rupee($total_paid,2) ?>
                </td>
            </tr>
            <tr>
                <td colspan="<?= $colspan ?>"
                    style="border: 2px solid #ddd; padding: 5px; text-align: right; font-size: 14px; font-weight: bold;">
                    TOTAL DUE
                </td>
                <td style="border: 2px solid #ddd; padding: 5px; text-align: right; font-size: 14px; font-weight: bold;">
                    <?= rupee($total_due,2) ?>
                </td>
            </tr>
            </tfoot>
        </table>
    </section>

    <!-- Payment Information -->
    <section style="margin-bottom: 20px;">
        <p style="margin: 0; font-size: 14px;"><strong>Payment Method :</strong></p>
        <p style="margin: 5px 0; font-size: 14px;">Account Name :</p>
        <p style="margin: 5px 0; font-size: 14px;">Bank/Credit Card</p>
        <p style="margin: 5px 0; font-size: 14px;">Paypal :<br></p>
    </section>

    <!-- Terms and Conditions -->
    <section style="margin-bottom: 40px;">
        <p style="font-size: 14px;"><strong>Terms & Conditions :</strong></p>
        <p style="font-size: 14px;">
            Please send payment within 30 days of receiving this invoice.<br>
            There will be a 1.5% interest charge per month on late invoices.
        </p>
    </section>

    <!-- Footer -->
    <footer style="text-align: left; padding-top: 20px; border-top: 2px solid #ddd;">
        <h3 style="color: #00a859; font-size: 18px;">Thank you for your business with us!</h3>
    </footer>
</div>
</body>
</html>
