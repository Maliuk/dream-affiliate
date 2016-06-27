<h3 class="da-title">Reports</h3>

<div class="da-inner-content da-partners">
    <input type="text" placeholder="Partner" />
    <input type="text" placeholder="Status" />

    <table>
        <thead>
            <tr>
                <th>Client Name</th>
                <th>Last Payment</th>
                <th>Date</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>

        <tbody>
            <?php
            //var_dump($da->getReports());
            ?>

            <?php
            $reports = $da->getReports();
            foreach ($reports as $report) {
                ?>
                <tr>
                    <td><?php echo $report->display_name; ?></td>
                    <td><?php echo $report->amount; ?>$</td>
                    <td>
                        <?php
                        $date = date_create($report->date);
                        echo date_format($date, 'd.m.Y');
                        ?>
                    </td>
                    <td class="da-active">Active</td>
                    <td>
                        <a href="#" class="da-action da-edit"></a>
                        <a href="#" class="da-action da-delete"></a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>