<h3 class="da-title">Partners</h3>

<div class="da-inner-content da-partners">
    <input type="text" placeholder="Enter Partners Name" />

    <table>
        <thead>
            <tr>
                <th>Partner name</th>
                <th>Joining Date</th>
                <th>Yearly Income</th>
                <th>Monthly Income</th>
                <th></th>
            </tr>
        </thead>

        <tbody>
            <?php
            $partners = $da->getPartners();
            ?>

            <?php foreach ($partners as $partner) { ?>
                <tr data-user-id="<?php echo $partner->ID; ?>">
                    <td><?php echo $partner->display_name; ?></td>
                    <td>
                        <?php
                        $date = date_create($partner->user_registered);
                        echo date_format($date, 'd.m.Y');
                        ?>
                    </td>
                    <td>1.000.000</td>
                    <td>10.000</td>
                    <td>
                        <a href="#" class="da-action da-profile"></a>
                        <a href="#" class="da-action da-edit"></a>
                        <a href="#" class="da-action da-delete"></a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>