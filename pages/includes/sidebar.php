<div id="da-sidebar">
    <ul>
        <li class="active"><a href="#dashboard" data-id="dashboard">Dashboard</a></li>
        <li><a href="#reports" data-id="reports">Reports</a></li>
        <?php if (current_user_can('administrator')) { ?>
            <li><a href="#partners" data-id="partners">Partners</a></li>
        <?php } ?>
        <li><a href="#clients" data-id="clients">Clients</a></li>
        <?php if (!current_user_can('administrator')) { ?>
            <li><a href="#marketing" data-id="marketing">Marketing Tool</a></li>
            <li><a href="#info" data-id="info">Personal Information</a></li>
        <?php } ?>
    </ul>
</div>