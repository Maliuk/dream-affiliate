<?php
if (isset($_COOKIE['da_affiliate'])) {
    //echo '<h1>' . $_COOKIE['da_affiliate'] . '</h1>';
}
?>

<h3 class="da-title">Dashboard</h3>

<div class="da-inner-content da-dashboard">
    
    <h6>Monthly data</h6>
    
    <ul class="da-bigicons-list">
        <li>
            <figure>
                <figcaption>
                    <div class="da-icon"></div>
                    Average Income
                </figcaption>
                <div class="da-figbody">
                    25.750
                </div>
                
            </figure>
        </li>
        
        <li>
            <figure>
                <figcaption>
                    <div class="da-icon"></div>
                    Monthly Income
                </figcaption>
                <div class="da-figbody">
                    <?php echo $da->getIncome(); ?>
                </div>
            </figure>
        </li>
        <li>
            <figure>
                <figcaption>
                    <div class="da-icon"></div>
                    Number of Cilents
                </figcaption>
                <div class="da-figbody">
                    <?php echo $da->getClientsCount(); ?>
                </div>
            </figure>
        </li>
        <li>
            <figure>
                <figcaption>
                    <div class="da-icon"></div>
                    Number of Partners
                </figcaption>
                <div class="da-figbody">
                    <?php echo $da->getPartnersCount(); ?>
                </div>
            </figure>
        </li>
    </ul>
    
    
    <h6>Total revenue</h6>
    
    <div id="chartTotalRevenue" style="width: 100%; height: 250px; overflow: hidden;">
        
    </div>
    
</div>