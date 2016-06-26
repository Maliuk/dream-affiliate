<h3 class="da-title">Clients</h3>

<?php
$clients = $da->getClients();
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

                <?php
                $isActive = $da->isClientActive($client->ID);

                $className = 'da-register';
                $status = 'Register';

                if ($isActive) {
                    $className = 'da-active';
                    $status = 'Active';
                }
                ?>

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
                    <td>
                        <?php
                        if ($isActive) {
                            $date = date_create($isActive->date);
                            echo date_format($date, 'd.m.Y');
                        }
                        //echo $isActive->date;
                        ?>
                    </td>
                    <td><?php echo $isActive ? $da->clientAmount($client->ID) : '0'; ?>$</td>
                    <td class="<?php echo $className; ?>"><?php echo $status; ?></td>
                    <td>
                        <a href="#" class="da-action da-delete"></a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>