<h3 class="da-title">Clients</h3>

<?php
$clients = $da->getClients();

//var_dump($clients);
?>

<div class="da-inner-content">
    <table>
        <thead>
            <tr>
                <th>Client Name</th>
                <th>Partner Name</th>
                <th>Registered Date</th>
                <th>Paid Date</th>
                <th>Paid Amount</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($clients as $client) { ?>
                <tr data-user-id="<?php echo $client->ID; ?>">
                    <td><?php echo $client->display_name ?></td>
                    <td>
                        <?php
                        $current_user = wp_get_current_user();
                        echo $current_user->display_name;
                        ?>
                    </td>
                    <td>
                        <?php
                        $date = date_create($client->user_registered);
                        echo date_format($date, 'd.m.Y');
                        ?>
                    </td>
                    <td>-</td>
                    <td>0$</td>
                    <td class="da-register">Register</td>
                    <td>
                        <a href="#" class="da-action da-delete"></a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>